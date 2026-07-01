<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class FirebaseAuthService
{
    private const CERTS_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    /**
     * @return array<string, mixed>
     */
    public function verifyIdToken(string $idToken): array
    {
        $projectId = config('services.firebase.project_id');

        if (! is_string($projectId) || $projectId === '') {
            throw ValidationException::withMessages([
                'firebase_id_token' => ['Firebase project ID is not configured.'],
            ]);
        }

        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            throw ValidationException::withMessages([
                'firebase_id_token' => ['Invalid Firebase ID token format.'],
            ]);
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $header = $this->decodeJsonSegment($encodedHeader, 'header');
        $payload = $this->decodeJsonSegment($encodedPayload, 'payload');

        if (($header['alg'] ?? null) !== 'RS256' || ! isset($header['kid'])) {
            throw ValidationException::withMessages([
                'firebase_id_token' => ['Invalid Firebase ID token header.'],
            ]);
        }

        $cert = $this->certificates()[$header['kid']] ?? null;
        if (! is_string($cert)) {
            throw ValidationException::withMessages([
                'firebase_id_token' => ['Firebase signing certificate was not found.'],
            ]);
        }

        $signature = $this->base64UrlDecode($encodedSignature);
        $verified = openssl_verify(
            $encodedHeader . '.' . $encodedPayload,
            $signature,
            $cert,
            OPENSSL_ALGO_SHA256,
        );

        if ($verified !== 1) {
            throw ValidationException::withMessages([
                'firebase_id_token' => ['Firebase ID token signature is invalid.'],
            ]);
        }

        $now = time();
        $issuer = 'https://securetoken.google.com/' . $projectId;

        if (($payload['iss'] ?? null) !== $issuer || ($payload['aud'] ?? null) !== $projectId) {
            throw ValidationException::withMessages([
                'firebase_id_token' => ['Firebase ID token is not for this project.'],
            ]);
        }

        if (! isset($payload['sub']) || ! is_string($payload['sub']) || $payload['sub'] === '' || strlen($payload['sub']) > 128) {
            throw ValidationException::withMessages([
                'firebase_id_token' => ['Firebase ID token subject is invalid.'],
            ]);
        }

        if (($payload['exp'] ?? 0) < $now || ($payload['iat'] ?? PHP_INT_MAX) > $now + 60) {
            throw ValidationException::withMessages([
                'firebase_id_token' => ['Firebase ID token is expired or not active yet.'],
            ]);
        }

        return $payload;
    }

    /**
     * @return array<string, string>
     */
    private function certificates(): array
    {
        return Cache::remember('firebase_securetoken_certificates', now()->addHours(6), function (): array {
            $response = Http::timeout(8)->get(self::CERTS_URL);

            if (! $response->successful()) {
                throw ValidationException::withMessages([
                    'firebase_id_token' => ['Could not fetch Firebase signing certificates.'],
                ]);
            }

            return $response->json() ?: [];
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonSegment(string $segment, string $name): array
    {
        $decoded = json_decode($this->base64UrlDecode($segment), true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                'firebase_id_token' => ["Invalid Firebase ID token {$name}."],
            ]);
        }

        return $decoded;
    }

    private function base64UrlDecode(string $value): string
    {
        $length = strlen($value);
        $padding = $length % 4 === 0 ? 0 : 4 - ($length % 4);
        $decoded = base64_decode(strtr($value . str_repeat('=', $padding), '-_', '+/'), true);

        if ($decoded === false) {
            throw ValidationException::withMessages([
                'firebase_id_token' => ['Firebase ID token contains invalid base64 data.'],
            ]);
        }

        return $decoded;
    }
}
