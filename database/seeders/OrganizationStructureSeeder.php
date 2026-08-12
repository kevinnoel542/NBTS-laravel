<?php

namespace Database\Seeders;

use App\Models\BloodCenter;
use App\Models\Department;
use App\Models\OrganizationUnit;
use App\Models\WorkLocation;
use App\OrganizationUnitStatus;
use App\OrganizationUnitType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationStructureSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $nationalUnit = OrganizationUnit::query()->updateOrCreate(
                ['code' => 'NBTS-TZ'],
                [
                    'parent_id' => null,
                    'name' => 'National Blood Transfusion Service Tanzania',
                    'short_name' => 'NBTS TZ',
                    'type' => OrganizationUnitType::National,
                    'status' => OrganizationUnitStatus::Active,
                    'effective_from' => null,
                    'effective_until' => null,
                ],
            );

            $this->seedDepartments($nationalUnit, [
                ['NAT-OPS', 'National Operations', 'National service coordination and center oversight.'],
                ['ICT-SEC', 'ICT & Security', 'Platform availability, access governance and security operations.'],
                ['NAT-QUALITY', 'Quality & Haemovigilance', 'National quality, traceability and haemovigilance oversight.'],
                ['NAT-LOGISTICS', 'Inventory & Logistics', 'National stock balancing and cold-chain coordination.'],
                ['ENGAGEMENT', 'Donor Engagement', 'Donor communication, campaigns and public content.'],
                ['GOVERNANCE', 'Data Governance', 'Privacy, audit and controlled information access.'],
            ]);

            BloodCenter::query()->orderBy('id')->each(function (BloodCenter $bloodCenter) use ($nationalUnit): void {
                $organizationUnit = OrganizationUnit::query()->updateOrCreate(
                    ['code' => sprintf('NBTS-BC-%04d', $bloodCenter->id)],
                    [
                        'parent_id' => $nationalUnit->id,
                        'name' => $bloodCenter->name,
                        'short_name' => $this->centerShortName($bloodCenter),
                        'type' => OrganizationUnitType::BloodCenter,
                        'status' => $bloodCenter->is_active
                            ? OrganizationUnitStatus::Active
                            : OrganizationUnitStatus::TemporarilyClosed,
                        'effective_from' => null,
                        'effective_until' => null,
                    ],
                );

                $bloodCenter->forceFill([
                    'organization_unit_id' => $organizationUnit->id,
                    'collection_identifier_prefix' => $bloodCenter->collection_identifier_prefix ?? sprintf('C%04d', $bloodCenter->id),
                    'daily_collection_capacity' => $bloodCenter->daily_collection_capacity ?? 120,
                ])->save();

                $this->seedDepartments($organizationUnit, [
                    ['RECEPTION', 'Reception & Registration', 'Donor arrival, identity matching and registration.'],
                    ['SCREENING', 'Screening & Counselling', 'Eligibility screening, counselling and deferral management.'],
                    ['COLLECTION', 'Collection', 'Donation collection and bedside traceability.'],
                    ['LABORATORY', 'Laboratory', 'Testing, result recording and release approval.'],
                    ['COMPONENTS', 'Component Processing', 'Blood component preparation and labelling.'],
                    ['INVENTORY', 'Inventory', 'Stock control, reservation and expiry monitoring.'],
                    ['LOGISTICS', 'Logistics & Cold Chain', 'Dispatch, transport and temperature control.'],
                    ['QUALITY', 'Quality & Haemovigilance', 'Center quality events, recalls and haemovigilance.'],
                ]);
            });
        }, attempts: 3);
    }

    /** @param list<array{0: string, 1: string, 2: string}> $definitions */
    private function seedDepartments(OrganizationUnit $organizationUnit, array $definitions): void
    {
        foreach ($definitions as [$code, $name, $description]) {
            $department = Department::query()->updateOrCreate(
                [
                    'organization_unit_id' => $organizationUnit->id,
                    'code' => $code,
                ],
                [
                    'name' => $name,
                    'description' => $description,
                    'is_active' => true,
                ],
            );

            WorkLocation::query()->updateOrCreate(
                [
                    'organization_unit_id' => $organizationUnit->id,
                    'code' => $code.'-MAIN',
                ],
                [
                    'department_id' => $department->id,
                    'name' => $name.' main station',
                    'type' => 'work_station',
                    'is_active' => true,
                ],
            );
        }
    }

    private function centerShortName(BloodCenter $bloodCenter): string
    {
        $shortName = Str::of($bloodCenter->name)
            ->replace('National Blood Transfusion Service - ', '')
            ->replace(' National Hospital Blood Bank', '')
            ->replace(' Hospital Blood Bank', '')
            ->replace(' Medical Centre Blood Bank', '')
            ->replace(' Blood Bank', '')
            ->limit(36, '')
            ->trim()
            ->toString();

        return $shortName !== '' ? $shortName : 'Center '.$bloodCenter->id;
    }
}
