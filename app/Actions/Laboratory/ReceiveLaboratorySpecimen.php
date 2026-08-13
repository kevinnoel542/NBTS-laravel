<?php

namespace App\Actions\Laboratory;

use App\LaboratorySpecimenReceiptStatus;
use App\LaboratoryTestOrderStatus;
use App\Models\LaboratorySpecimenReceipt;
use App\Models\LaboratoryTestCatalog;
use App\Models\LaboratoryTestOrder;
use App\Models\Specimen;
use App\Models\User;
use App\PermissionName;
use App\SpecimenStatus;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class ReceiveLaboratorySpecimen
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(User $actor, Specimen $specimen, string $scannedIdentifier, string $receivingStation): LaboratorySpecimenReceipt
    {
        return DB::transaction(function () use ($actor, $specimen, $scannedIdentifier, $receivingStation): LaboratorySpecimenReceipt {
            $record = Specimen::query()
                ->with(['collectionEpisode.bloodCenter'])
                ->lockForUpdate()
                ->findOrFail($specimen->id);

            Gate::forUser($actor)->authorize('view', $record->collectionEpisode);

            if (! $actor->can(PermissionName::RecordLaboratoryTests->value)) {
                throw ValidationException::withMessages(['laboratory' => ['This account cannot receive laboratory specimens.']]);
            }

            if ($record->status !== SpecimenStatus::HandedOff
                || ! hash_equals($record->specimen_identifier, trim($scannedIdentifier))
                || mb_strlen(trim($receivingStation)) < 3) {
                throw ValidationException::withMessages(['specimen' => ['Only a handed-off specimen with a matching barcode can be received by the laboratory.']]);
            }

            if (LaboratorySpecimenReceipt::query()->where('specimen_id', $record->id)->exists()) {
                throw ValidationException::withMessages(['specimen' => ['This specimen has already been received by the laboratory.']]);
            }

            $receipt = LaboratorySpecimenReceipt::query()->create([
                'specimen_id' => $record->id,
                'collection_episode_id' => $record->collection_episode_id,
                'collection_container_id' => $record->collection_container_id,
                'blood_center_id' => $record->collectionEpisode->blood_center_id,
                'received_by' => $actor->id,
                'scanned_identifier' => trim($scannedIdentifier),
                'receiving_station' => trim($receivingStation),
                'status' => LaboratorySpecimenReceiptStatus::Accepted,
                'received_at' => now(),
            ]);

            $catalogs = LaboratoryTestCatalog::query()
                ->where('specimen_type', $record->specimen_type)
                ->where('is_active', true)
                ->where('is_required_for_release', true)
                ->whereDate('effective_from', '<=', today())
                ->orderBy('code')
                ->get();

            foreach ($catalogs as $catalog) {
                LaboratoryTestOrder::query()->create([
                    'laboratory_specimen_receipt_id' => $receipt->id,
                    'specimen_id' => $record->id,
                    'laboratory_test_catalog_id' => $catalog->id,
                    'ordered_by' => $actor->id,
                    'status' => LaboratoryTestOrderStatus::Ordered,
                    'ordered_at' => now(),
                    'due_at' => now()->addDay(),
                ]);
            }

            $this->auditLogger->record($actor, 'laboratory.specimen_received', $receipt, $record->collectionEpisode->bloodCenter, [
                'specimen_identifier' => $record->specimen_identifier,
                'orders_created' => $catalogs->count(),
                'station' => $receipt->receiving_station,
            ]);

            return $receipt->fresh(['orders.testCatalog', 'specimen', 'collectionEpisode']);
        }, attempts: 3);
    }
}
