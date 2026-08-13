<?php

namespace App\Actions\Laboratory;

use App\LaboratoryTestCategory;
use App\Models\LaboratoryTestCatalog;
use App\Models\User;
use App\PermissionName;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class ApproveLaboratoryTestCatalog
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  list<string>  $releaseBlockingInterpretations
     */
    public function handle(
        User $actor,
        string $code,
        string $name,
        LaboratoryTestCategory $category,
        string $specimenType,
        string $method,
        string $algorithmVersion,
        bool $isRequiredForRelease,
        array $releaseBlockingInterpretations,
    ): LaboratoryTestCatalog {
        Gate::forUser($actor)->authorize(PermissionName::ManageQuality->value);

        if (trim($code) === '' || trim($name) === '' || trim($method) === '' || trim($algorithmVersion) === '' || trim($specimenType) === '') {
            throw ValidationException::withMessages(['catalog' => ['Approved laboratory test catalog entries require code, name, method, algorithm version, and specimen type.']]);
        }

        $catalog = LaboratoryTestCatalog::query()->create([
            'code' => strtoupper(trim($code)),
            'name' => trim($name),
            'category' => $category,
            'specimen_type' => trim($specimenType),
            'method' => trim($method),
            'algorithm_version' => trim($algorithmVersion),
            'release_blocking_interpretations' => array_values(array_unique($releaseBlockingInterpretations)),
            'is_required_for_release' => $isRequiredForRelease,
            'is_active' => true,
            'effective_from' => today(),
            'approved_at' => now(),
            'approved_by' => $actor->id,
        ]);

        $this->auditLogger->record($actor, 'laboratory.catalog_approved', $catalog, metadata: [
            'code' => $catalog->code,
            'algorithm_version' => $catalog->algorithm_version,
            'required_for_release' => $catalog->is_required_for_release,
        ]);

        return $catalog;
    }
}
