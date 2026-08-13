<?php

namespace App\Services;

use App\ColdChainAlarmStatus;
use App\ColdChainDeviceStatus;
use App\ColdChainExcursionStatus;
use App\ComponentStatus;
use App\Models\BloodComponent;
use App\Models\ColdChainAlarm;
use App\Models\ColdChainDevice;
use App\Models\ColdChainExcursion;
use App\Models\ColdChainTemperatureReading;
use App\Models\User;
use App\PermissionName;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ColdChainMonitoringService
{
    public function recordReading(
        ColdChainDevice $device,
        float $temperature,
        ?User $actor = null,
        string $syncState = 'manual',
        array $payload = [],
    ): ColdChainTemperatureReading {
        if ($actor instanceof User && ! $actor->can(PermissionName::ManageColdChain->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot manage cold-chain records.']]);
        }

        return DB::transaction(function () use ($device, $temperature, $actor, $syncState, $payload): ColdChainTemperatureReading {
            $record = ColdChainDevice::query()->lockForUpdate()->findOrFail($device->id);
            $reading = ColdChainTemperatureReading::query()->create([
                'cold_chain_device_id' => $record->id,
                'payload' => $payload,
                'recorded_at' => now(),
                'recorded_by' => $actor?->id,
                'sync_state' => $syncState,
                'temperature_c' => $temperature,
            ]);

            if ($temperature < (float) $record->temperature_min_c || $temperature > (float) $record->temperature_max_c) {
                $this->openExcursion($record, $temperature, $actor);
            }

            return $reading;
        }, attempts: 3);
    }

    public function openExcursion(ColdChainDevice $device, float $temperature, ?User $actor = null): ColdChainExcursion
    {
        $affectedComponents = BloodComponent::query()
            ->lockForUpdate()
            ->where('blood_center_id', $device->blood_center_id)
            ->where(function ($query) use ($device): void {
                $query
                    ->where('cold_chain_device_id', $device->id)
                    ->orWhere('storage_location', $device->location);
            })
            ->whereIn('status', [ComponentStatus::Available, ComponentStatus::Reserved, ComponentStatus::Released])
            ->get();

        foreach ($affectedComponents as $component) {
            $component->forceFill([
                'investigation_hold_at' => now(),
                'status' => ComponentStatus::InvestigationHold,
            ])->save();
        }

        $device->forceFill(['status' => ColdChainDeviceStatus::Alarm])->save();

        $excursion = ColdChainExcursion::query()->create([
            'affected_component_ids' => $affectedComponents->pluck('id')->values()->all(),
            'cold_chain_device_id' => $device->id,
            'observed_max_c' => $temperature,
            'observed_min_c' => $temperature,
            'opened_at' => now(),
            'opened_by' => $actor?->id,
            'started_at' => now(),
            'status' => ColdChainExcursionStatus::Open,
        ]);

        ColdChainAlarm::query()->create([
            'cold_chain_device_id' => $device->id,
            'cold_chain_excursion_id' => $excursion->id,
            'observed_max_c' => $temperature,
            'observed_min_c' => $temperature,
            'response_target_at' => now()->addMinutes((int) ($device->alarm_config['response_minutes'] ?? 30)),
            'status' => ColdChainAlarmStatus::Open,
            'summary' => 'Temperature excursion detected',
            'threshold_max_c' => $device->temperature_max_c,
            'threshold_min_c' => $device->temperature_min_c,
            'triggered_at' => now(),
        ]);

        return $excursion;
    }

    public function closeExcursion(ColdChainExcursion $excursion, User $qualityActor, string $disposition, string $capa): ColdChainExcursion
    {
        if (! $qualityActor->can(PermissionName::ManageQuality->value) && ! $qualityActor->can(PermissionName::ManageColdChain->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot close cold-chain excursions.']]);
        }

        if (mb_strlen(trim($disposition)) < 5 || mb_strlen(trim($capa)) < 5) {
            throw ValidationException::withMessages(['excursion' => ['Excursion closure requires disposition and CAPA.']]);
        }

        $record = ColdChainExcursion::query()->findOrFail($excursion->id);
        $record->forceFill([
            'capa' => trim($capa),
            'closed_at' => now(),
            'closed_by' => $qualityActor->id,
            'disposition' => trim($disposition),
            'ended_at' => now(),
            'status' => ColdChainExcursionStatus::Closed,
        ])->save();

        return $record->refresh();
    }
}
