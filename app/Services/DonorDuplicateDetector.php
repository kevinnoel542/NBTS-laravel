<?php

namespace App\Services;

use App\Models\User;
use App\RoleName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class DonorDuplicateDetector
{
    /**
     * @param  array{name?: string|null, phone?: string|null, email?: string|null, date_of_birth?: string|null, region?: string|null}  $identity
     * @return Collection<int, array{donor: User, score: float, signals: array<string, bool>}>
     */
    public function matches(array $identity, ?int $excludeUserId = null): Collection
    {
        $normalized = $this->normalize($identity);

        if ($normalized['phone'] === null
            && $normalized['email'] === null
            && ($normalized['name'] === null || $normalized['date_of_birth'] === null)) {
            return collect();
        }

        return User::query()
            ->whereHas('roles', fn (Builder $query): Builder => $query->where('name', RoleName::Donor->value))
            ->when($excludeUserId !== null, fn (Builder $query): Builder => $query->where('id', '!=', $excludeUserId))
            ->where(function (Builder $query) use ($normalized): void {
                if ($normalized['phone'] !== null) {
                    $query->orWhere('phone', $normalized['phone']);
                }

                if ($normalized['email'] !== null) {
                    $query->orWhere('email', $normalized['email']);
                }

                if ($normalized['name'] !== null && $normalized['date_of_birth'] !== null) {
                    $query->orWhere(function (Builder $identityQuery) use ($normalized): void {
                        $identityQuery
                            ->whereDate('date_of_birth', $normalized['date_of_birth'])
                            ->where('name', 'like', '%'.addcslashes(Str::beforeLast($normalized['name'], ' '), '\%_').'%');
                    });
                }
            })
            ->with('donorProfile:id,user_id,donor_id,preferred_center_id,identity_review_required')
            ->limit(50)
            ->get()
            ->map(function (User $donor) use ($normalized): array {
                $candidate = $this->normalize([
                    'name' => $donor->name,
                    'phone' => $donor->phone,
                    'email' => $donor->email,
                    'date_of_birth' => $donor->date_of_birth?->toDateString(),
                    'region' => $donor->region,
                ]);
                $signals = [
                    'phone' => $normalized['phone'] !== null && $normalized['phone'] === $candidate['phone'],
                    'email' => $normalized['email'] !== null && $normalized['email'] === $candidate['email'],
                    'name' => $normalized['name'] !== null && $normalized['name'] === $candidate['name'],
                    'date_of_birth' => $normalized['date_of_birth'] !== null && $normalized['date_of_birth'] === $candidate['date_of_birth'],
                    'region' => $normalized['region'] !== null && $normalized['region'] === $candidate['region'],
                ];
                $score = ($signals['phone'] ? 55 : 0)
                    + ($signals['email'] ? 55 : 0)
                    + ($signals['name'] ? 25 : 0)
                    + ($signals['date_of_birth'] ? 25 : 0)
                    + ($signals['region'] ? 5 : 0);

                return $this->matchResult($donor, (float) min(100, $score), $signals);
            })
            ->filter(fn (array $match): bool => $match['score'] >= 40)
            ->sortByDesc('score')
            ->values();
    }

    /**
     * @param  array{name?: string|null, phone?: string|null, email?: string|null, date_of_birth?: string|null, region?: string|null}  $identity
     * @return array{name: string|null, phone: string|null, email: string|null, date_of_birth: string|null, region: string|null}
     */
    private function normalize(array $identity): array
    {
        return [
            'name' => $this->normalizedText($identity['name'] ?? null),
            'phone' => $this->normalizedPhone($identity['phone'] ?? null),
            'email' => $this->normalizedText($identity['email'] ?? null),
            'date_of_birth' => $this->nullableString($identity['date_of_birth'] ?? null),
            'region' => $this->normalizedText($identity['region'] ?? null),
        ];
    }

    private function normalizedText(?string $value): ?string
    {
        $value = Str::of((string) $value)->squish()->lower()->value();

        return $value === '' ? null : $value;
    }

    private function normalizedPhone(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if (! is_string($digits) || $digits === '') {
            return null;
        }

        if (Str::startsWith($digits, '255')) {
            return '+'.$digits;
        }

        if (Str::startsWith($digits, '0') && strlen($digits) === 10) {
            return '+255'.substr($digits, 1);
        }

        return '+'.$digits;
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, bool>  $signals
     * @return array{donor: User, score: float, signals: array<string, bool>}
     */
    private function matchResult(User $donor, float $score, array $signals): array
    {
        return ['donor' => $donor, 'score' => $score, 'signals' => $signals];
    }
}
