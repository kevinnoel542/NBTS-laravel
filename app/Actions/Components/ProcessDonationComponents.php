<?php

namespace App\Actions\Components;

use App\ComponentStatus;
use App\Models\BloodComponent;
use App\Models\BloodUnit;
use App\Models\ComponentProcessingEvent;
use App\Models\ComponentProductCatalog;
use App\Models\User;
use App\PermissionName;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ProcessDonationComponents
{
    /**
     * @param  list<array{
     *     catalog: ComponentProductCatalog,
     *     parent_component_id?: int|null,
     *     special_attributes?: array<string, mixed>,
     *     storage_location?: string|null
     * }>  $components
     */
    public function execute(
        BloodUnit $bloodUnit,
        User $operator,
        string $method,
        array $components,
        ?string $deviceIdentifier = null,
        array $yieldSummary = [],
        array $modifications = [],
        array $qcSamples = [],
        array $deviations = [],
        bool $finalLabelVerified = true,
    ): ComponentProcessingEvent {
        if (! $operator->can(PermissionName::ProcessComponents->value)) {
            throw ValidationException::withMessages(['operator' => ['This account cannot process blood components.']]);
        }

        if ($components === [] || trim($method) === '' || ! $finalLabelVerified) {
            throw ValidationException::withMessages(['components' => ['Component processing requires at least one product, method, and final label verification.']]);
        }

        return DB::transaction(function () use ($bloodUnit, $operator, $method, $components, $deviceIdentifier, $yieldSummary, $modifications, $qcSamples, $deviations, $finalLabelVerified): ComponentProcessingEvent {
            $unit = BloodUnit::query()
                ->with(['donation', 'components'])
                ->lockForUpdate()
                ->findOrFail($bloodUnit->id);

            $event = ComponentProcessingEvent::query()->create([
                'blood_unit_id' => $unit->id,
                'deviations' => $deviations,
                'device_identifier' => $deviceIdentifier,
                'donation_id' => $unit->donation_id,
                'ended_at' => now(),
                'event_type' => 'component_production',
                'final_label_verified' => $finalLabelVerified,
                'method' => trim($method),
                'modifications' => $modifications,
                'operator_id' => $operator->id,
                'qc_samples' => $qcSamples,
                'started_at' => now()->subMinutes(30),
                'yield_summary' => $yieldSummary,
            ]);

            foreach ($components as $index => $componentSpec) {
                $catalog = $componentSpec['catalog'];
                $catalog = ComponentProductCatalog::query()->lockForUpdate()->findOrFail($catalog->id);

                if (! $catalog->is_active || $catalog->effective_from->isFuture()) {
                    throw ValidationException::withMessages(['catalog' => ['Component product catalog must be active and effective.']]);
                }

                $parentComponentId = $componentSpec['parent_component_id'] ?? null;

                if ($parentComponentId !== null) {
                    $parent = BloodComponent::query()->findOrFail($parentComponentId);

                    if ($parent->blood_unit_id !== $unit->id || $parent->donation_id !== $unit->donation_id) {
                        throw ValidationException::withMessages(['parent_component_id' => ['Parent component must belong to the same donation and original unit.']]);
                    }
                }

                BloodComponent::query()->create([
                    'blood_center_id' => $unit->blood_center_id,
                    'blood_group' => $unit->blood_group,
                    'blood_unit_id' => $unit->id,
                    'component_processing_event_id' => $event->id,
                    'component_product_catalog_id' => $catalog->id,
                    'donation_id' => $unit->donation_id,
                    'expiry_date' => today()->addDays($catalog->shelf_life_days),
                    'parent_component_id' => $parentComponentId,
                    'processed_at' => now(),
                    'product_identifier' => $this->productIdentifier($unit, $catalog, $index + 1),
                    'special_attributes' => $componentSpec['special_attributes'] ?? $catalog->special_attributes ?? [],
                    'status' => ComponentStatus::Quarantined,
                    'storage_location' => $componentSpec['storage_location'] ?? 'Component quarantine',
                ]);
            }

            return $event->fresh(['components.productCatalog', 'bloodUnit']);
        }, attempts: 3);
    }

    private function productIdentifier(BloodUnit $unit, ComponentProductCatalog $catalog, int $sequence): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9-]/i', '', $unit->unit_number.'-'.$catalog->code.'-'.$sequence));
    }
}
