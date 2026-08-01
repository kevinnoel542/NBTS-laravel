<?php

namespace App\Services;

use App\Models\DonorProfile;
use App\RoleName;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonException;
use Throwable;

final class DonorCardQrService
{
    /**
     * @return array{payload: string, expires_at: CarbonImmutable}
     */
    public function issue(DonorProfile $profile, ?CarbonImmutable $expiresAt = null): array
    {
        $issuedAt = CarbonImmutable::now((string) config('app.timezone'));
        $expiresAt ??= $issuedAt->addSeconds(
            max((int) config('nbts.donor_card_qr_ttl_seconds', 300), 60),
        );
        $data = [
            'type' => 'nbts_donor_card',
            'version' => 1,
            'donor_id' => $profile->donor_id,
            'user_id' => $profile->user_id,
            'nonce' => Str::random(24),
            'issued_at' => $issuedAt->toIso8601String(),
            'expires_at' => $expiresAt->toIso8601String(),
        ];
        $encodedPayload = $this->base64UrlEncode(json_encode($data, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $encodedPayload, $this->signingKey());

        return [
            'payload' => "nbtsqr.{$encodedPayload}.{$signature}",
            'expires_at' => $expiresAt,
        ];
    }

    public function verify(string $signedPayload): DonorProfile
    {
        $parts = explode('.', $signedPayload);

        if (count($parts) !== 3 || $parts[0] !== 'nbtsqr') {
            $this->invalid('api.donor_card_qr_invalid');
        }

        $encodedPayload = $parts[1];
        $signature = $parts[2];
        $expectedSignature = hash_hmac('sha256', $encodedPayload, $this->signingKey());

        if (! hash_equals($expectedSignature, $signature)) {
            $this->invalid('api.donor_card_qr_invalid');
        }

        try {
            $decodedPayload = $this->base64UrlDecode($encodedPayload);
            $data = json_decode($decodedPayload, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->invalid('api.donor_card_qr_invalid');
        }

        if (! is_array($data)
            || ($data['type'] ?? null) !== 'nbts_donor_card'
            || ($data['version'] ?? null) !== 1
            || ! is_string($data['donor_id'] ?? null)
            || ! is_int($data['user_id'] ?? null)
            || ! is_string($data['expires_at'] ?? null)) {
            $this->invalid('api.donor_card_qr_invalid');
        }

        try {
            $expiresAt = CarbonImmutable::parse($data['expires_at']);
        } catch (Throwable) {
            $this->invalid('api.donor_card_qr_invalid');
        }

        if ($expiresAt->lessThanOrEqualTo(CarbonImmutable::now())) {
            $this->invalid('api.donor_card_qr_expired');
        }

        $profile = DonorProfile::query()
            ->with(['user', 'preferredCenter'])
            ->where('donor_id', $data['donor_id'])
            ->where('user_id', $data['user_id'])
            ->first();

        if ($profile === null
            || ! $profile->user->is_active
            || ! $profile->user->hasRole(RoleName::Donor->value)) {
            $this->invalid('api.donor_card_qr_not_found');
        }

        return $profile;
    }

    private function signingKey(): string
    {
        $configuredKey = config('nbts.donor_card_qr_signing_key');

        return is_string($configuredKey) && $configuredKey !== ''
            ? $configuredKey
            : (string) config('app.key');
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $paddingLength = (4 - strlen($value) % 4) % 4;
        $decoded = base64_decode(
            strtr($value.str_repeat('=', $paddingLength), '-_', '+/'),
            strict: true,
        );

        if ($decoded === false) {
            $this->invalid('api.donor_card_qr_invalid');
        }

        return $decoded;
    }

    private function invalid(string $translationKey): never
    {
        throw ValidationException::withMessages([
            'qr_payload' => [__($translationKey)],
        ]);
    }
}
