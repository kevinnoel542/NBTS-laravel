<?php

namespace Database\Factories;

use App\Models\NotificationDelivery;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationDelivery>
 */
class NotificationDeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_notification_id' => UserNotification::factory(),
            'user_id' => function (array $attributes): int {
                return UserNotification::query()
                    ->whereKey($attributes['user_notification_id'])
                    ->firstOrFail()
                    ->user_id;
            },
            'channel' => 'in_app',
            'status' => 'delivered',
            'attempts' => 1,
            'provider' => 'database',
            'provider_message_id' => null,
            'last_error' => null,
            'attempted_at' => now(),
            'delivered_at' => now(),
            'failed_at' => null,
        ];
    }
}
