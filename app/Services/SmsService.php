<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * @return array{sent: bool, provider: string, provider_message_id?: string|null, error?: string|null}
     */
    public function send(string $phone, string $message): array
    {
        $driver = (string) config('services.sms.driver', 'log');

        return match ($driver) {
            'africas_talking' => $this->sendViaAfricasTalking($phone, $message),
            'beem' => $this->sendViaBeem($phone, $message),
            'twilio' => $this->sendViaTwilio($phone, $message),
            'log' => $this->sendViaLog($phone, $message),
            default => [
                'sent' => false,
                'provider' => $driver,
                'error' => "Unsupported SMS driver [{$driver}].",
            ],
        };
    }

    private function sendViaLog(string $phone, string $message): array
    {
        Log::info('SMS reminder prepared.', [
            'phone' => $phone,
            'message' => $message,
        ]);

        return [
            'sent' => true,
            'provider' => 'log',
            'provider_message_id' => null,
        ];
    }

    private function sendViaAfricasTalking(string $phone, string $message): array
    {
        $username = config('services.sms.africas_talking.username');
        $apiKey = config('services.sms.africas_talking.api_key');
        $senderId = config('services.sms.africas_talking.sender_id');

        if (! $username || ! $apiKey) {
            return [
                'sent' => false,
                'provider' => 'africas_talking',
                'error' => 'Africa’s Talking SMS credentials are not configured.',
            ];
        }

        $response = Http::asForm()
            ->withHeaders(['apiKey' => $apiKey])
            ->post('https://api.africastalking.com/version1/messaging', array_filter([
                'username' => $username,
                'to' => $phone,
                'message' => $message,
                'from' => $senderId,
            ]));

        return $this->httpResult('africas_talking', $response->successful(), $response->json() ?: $response->body());
    }

    private function sendViaBeem(string $phone, string $message): array
    {
        $apiKey = config('services.sms.beem.api_key');
        $secretKey = config('services.sms.beem.secret_key');
        $sourceAddr = config('services.sms.beem.source_addr');

        if (! $apiKey || ! $secretKey) {
            return [
                'sent' => false,
                'provider' => 'beem',
                'error' => 'Beem SMS credentials are not configured.',
            ];
        }

        $response = Http::withBasicAuth($apiKey, $secretKey)
            ->post('https://apisms.beem.africa/v1/send', [
                'source_addr' => $sourceAddr,
                'schedule_time' => '',
                'encoding' => 0,
                'message' => $message,
                'recipients' => [
                    [
                        'recipient_id' => '1',
                        'dest_addr' => $phone,
                    ],
                ],
            ]);

        return $this->httpResult('beem', $response->successful(), $response->json() ?: $response->body());
    }

    private function sendViaTwilio(string $phone, string $message): array
    {
        $sid = config('services.sms.twilio.sid');
        $token = config('services.sms.twilio.token');
        $from = config('services.sms.twilio.from');

        if (! $sid || ! $token || ! $from) {
            return [
                'sent' => false,
                'provider' => 'twilio',
                'error' => 'Twilio SMS credentials are not configured.',
            ];
        }

        $response = Http::asForm()
            ->withBasicAuth($sid, $token)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To' => $phone,
                'Body' => $message,
            ]);

        return $this->httpResult('twilio', $response->successful(), $response->json() ?: $response->body());
    }

    /**
     * @param mixed $body
     * @return array{sent: bool, provider: string, provider_message_id?: string|null, error?: string|null}
     */
    private function httpResult(string $provider, bool $successful, mixed $body): array
    {
        if ($successful) {
            return [
                'sent' => true,
                'provider' => $provider,
                'provider_message_id' => is_array($body)
                    ? (string) data_get($body, 'SMSMessageData.Recipients.0.messageId', data_get($body, 'messages.0.uid', data_get($body, 'sid', '')))
                    : null,
            ];
        }

        return [
            'sent' => false,
            'provider' => $provider,
            'error' => is_string($body) ? $body : json_encode($body),
        ];
    }
}
