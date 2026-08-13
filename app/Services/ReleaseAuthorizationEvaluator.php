<?php

namespace App\Services;

use App\LaboratoryQualityControlStatus;
use App\LaboratoryTestResultStatus;
use App\Models\BloodUnit;
use App\Models\LaboratoryTestResult;

class ReleaseAuthorizationEvaluator
{
    public const CRITERIA_VERSION = 'NBTS-P7-REL-AUTH-v1';

    /** @var list<string> */
    public const REQUIRED_TEST_CODES = [
        'ABO-RH',
        'HIV-1-2',
        'HBSAG',
        'HCV',
        'SYPHILIS',
    ];

    /**
     * @return array{
     *     criteria_version: string,
     *     eligible: bool,
     *     evaluated_tests: list<array<string, mixed>>,
     *     exceptions: list<string>,
     *     test_actor_ids: list<int>
     * }
     */
    public function evaluate(BloodUnit $bloodUnit): array
    {
        $results = LaboratoryTestResult::query()
            ->with(['testCatalog', 'qualityControlRun', 'enteredBy', 'verifier'])
            ->whereHas('order.receipt.collectionEpisode', function ($query) use ($bloodUnit): void {
                $query->where('donation_id', $bloodUnit->donation_id);
            })
            ->get()
            ->keyBy(fn (LaboratoryTestResult $result): string => strtoupper($result->testCatalog->code));

        $evaluatedTests = [];
        $exceptions = [];
        $testActorIds = [];

        foreach (self::REQUIRED_TEST_CODES as $testCode) {
            /** @var LaboratoryTestResult|null $result */
            $result = $results->get($testCode);

            if (! $result instanceof LaboratoryTestResult) {
                $exceptions[] = "missing_required_test:{$testCode}";
                $evaluatedTests[] = [
                    'status' => 'missing',
                    'test_code' => $testCode,
                ];

                continue;
            }

            $testActorIds[] = $result->entered_by;

            if ($result->verified_by !== null) {
                $testActorIds[] = $result->verified_by;
            }

            $resultExceptions = $this->resultExceptions($result);
            $exceptions = [...$exceptions, ...$resultExceptions];

            $evaluatedTests[] = [
                'algorithm_version' => $result->testCatalog->algorithm_version,
                'exceptions' => $resultExceptions,
                'interpretation' => $result->interpretation->value,
                'is_release_blocking' => $result->is_release_blocking,
                'method' => $result->testCatalog->method,
                'quality_control_status' => $result->qualityControlRun->status->value,
                'result_status' => $result->status->value,
                'result_value' => $result->result_value,
                'test_code' => strtoupper($result->testCatalog->code),
                'test_name' => $result->testCatalog->name,
                'verified_by' => $result->verified_by,
            ];
        }

        return [
            'criteria_version' => self::CRITERIA_VERSION,
            'eligible' => $exceptions === [],
            'evaluated_tests' => $evaluatedTests,
            'exceptions' => array_values(array_unique($exceptions)),
            'test_actor_ids' => $this->uniqueIntegers($testActorIds),
        ];
    }

    /**
     * @return list<string>
     */
    private function resultExceptions(LaboratoryTestResult $result): array
    {
        $testCode = strtoupper($result->testCatalog->code);
        $exceptions = [];

        if ($result->status === LaboratoryTestResultStatus::Invalid) {
            $exceptions[] = "invalid_result:{$testCode}";
        }

        if ($result->status === LaboratoryTestResultStatus::Repeated) {
            $exceptions[] = "repeated_result:{$testCode}";
        }

        if ($result->status !== LaboratoryTestResultStatus::Verified || $result->verified_by === null || $result->verified_at === null) {
            $exceptions[] = "unverified_result:{$testCode}";
        }

        if ($result->qualityControlRun->status !== LaboratoryQualityControlStatus::Passed) {
            $exceptions[] = "quality_control_not_acceptable:{$testCode}";
        }

        if ($result->is_release_blocking) {
            $exceptions[] = "unsafe_result:{$testCode}";
        }

        if ($result->verified_by !== null && $result->entered_by === $result->verified_by) {
            $exceptions[] = "tester_verifier_not_separated:{$testCode}";
        }

        return $exceptions;
    }

    /**
     * @param  list<int|null>  $values
     * @return list<int>
     */
    private function uniqueIntegers(array $values): array
    {
        $uniqueValues = [];

        foreach ($values as $value) {
            if ($value !== null && ! in_array($value, $uniqueValues, true)) {
                $uniqueValues[] = $value;
            }
        }

        return $uniqueValues;
    }
}
