<?php

namespace App\Services;

use App\Models\DonorProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DonorQrCodeService
{
    public function makePayload(DonorProfile $profile, ?Carbon $expiresAt = null): array
    {
        $expiresAt ??= now()->addDay();

        $data = [
            'type' => 'nbts_donor_card',
            'version' => 1,
            'donor_id' => $profile->donor_id,
            'user_id' => $profile->user_id,
            'issued_at' => now()->toISOString(),
            'expires_at' => $expiresAt->toISOString(),
        ];

        $payload = $this->base64UrlEncode(json_encode($data, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $payload, config('app.key'));

        return [
            'payload' => 'nbtsqr.' . $payload . '.' . $signature,
            'expires_at' => $expiresAt,
        ];
    }

    public function verify(string $qrPayload): DonorProfile
    {
        $parts = explode('.', $qrPayload);

        if (count($parts) !== 3 || $parts[0] !== 'nbtsqr') {
            throw ValidationException::withMessages([
                'qr_payload' => ['Invalid donor QR code.'],
            ]);
        }

        [$prefix, $payload, $signature] = $parts;
        $expectedSignature = hash_hmac('sha256', $payload, config('app.key'));

        if (! hash_equals($expectedSignature, $signature)) {
            throw ValidationException::withMessages([
                'qr_payload' => ['This donor QR code signature is invalid.'],
            ]);
        }

        $data = json_decode($this->base64UrlDecode($payload), true);

        if (! is_array($data) || ($data['type'] ?? null) !== 'nbts_donor_card') {
            throw ValidationException::withMessages([
                'qr_payload' => ['This is not an NBTS donor QR code.'],
            ]);
        }

        if (empty($data['expires_at']) || Carbon::parse($data['expires_at'])->isPast()) {
            throw ValidationException::withMessages([
                'qr_payload' => ['This donor QR code has expired. Ask the donor to refresh their card.'],
            ]);
        }

        $profile = DonorProfile::query()
            ->with(['user', 'preferredCenter'])
            ->where('donor_id', $data['donor_id'] ?? null)
            ->where('user_id', $data['user_id'] ?? null)
            ->first();

        if (! $profile) {
            throw ValidationException::withMessages([
                'qr_payload' => ['Donor account was not found.'],
            ]);
        }

        return $profile;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/'));
    }
}
