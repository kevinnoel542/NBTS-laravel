<?php

namespace App\Services;

use App\Models\BloodCenter;
use App\Models\CollectionIdentifierSequence;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CollectionIdentifierService
{
    public function next(BloodCenter $bloodCenter, ?int $year = null): string
    {
        $range = $this->reserve($bloodCenter, 1, $year);

        return $this->format($bloodCenter, $range['year'], $range['start']);
    }

    /** @return array{year: int, start: int, end: int} */
    public function reserve(BloodCenter $bloodCenter, int $size, ?int $year = null): array
    {
        if ($size < 1 || $size > 1000) {
            throw ValidationException::withMessages(['batch_size' => ['Identifier batch size must be between 1 and 1000.']]);
        }

        $year ??= (int) now()->format('Y');

        return DB::transaction(function () use ($bloodCenter, $size, $year): array {
            $sequence = CollectionIdentifierSequence::query()
                ->where('blood_center_id', $bloodCenter->id)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                $sequence = CollectionIdentifierSequence::query()->create([
                    'blood_center_id' => $bloodCenter->id,
                    'year' => $year,
                    'last_sequence' => 0,
                ]);
            }

            $start = $sequence->last_sequence + 1;
            $end = $sequence->last_sequence + $size;
            $sequence->forceFill(['last_sequence' => $end])->save();

            return ['year' => $year, 'start' => $start, 'end' => $end];
        }, attempts: 3);
    }

    public function format(BloodCenter $bloodCenter, int $year, int $sequence): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $bloodCenter->collection_identifier_prefix));

        if ($prefix === '') {
            throw ValidationException::withMessages(['blood_center' => ['The center requires a unique collection identifier prefix.']]);
        }

        $body = strtoupper((string) config('phase-six.identifiers.country_prefix', 'TZ'))
            .$prefix.$year.str_pad((string) $sequence, 7, '0', STR_PAD_LEFT);

        return $body.$this->checkCharacter($body);
    }

    public function validate(BloodCenter $bloodCenter, string $identifier): bool
    {
        $identifier = strtoupper(trim($identifier));
        $prefix = strtoupper((string) config('phase-six.identifiers.country_prefix', 'TZ'))
            .strtoupper((string) $bloodCenter->collection_identifier_prefix);

        if (! str_starts_with($identifier, $prefix) || strlen($identifier) < strlen($prefix) + 12) {
            return false;
        }

        $body = substr($identifier, 0, -1);

        return hash_equals($this->checkCharacter($body), substr($identifier, -1));
    }

    private function checkCharacter(string $body): string
    {
        $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ*';
        $checksum = 0;

        foreach (str_split($body) as $index => $character) {
            $value = strpos($alphabet, $character);
            $checksum += ($value === false ? 0 : $value) * ($index + 1);
        }

        return $alphabet[$checksum % strlen($alphabet)];
    }
}
