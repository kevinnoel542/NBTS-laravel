<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $blood_center_id
 * @property string $scheduled_at
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BloodCenter $bloodCenter
 * @property-read \App\Models\Donation|null $donation
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereBloodCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereUserId($value)
 */
	class Appointment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string|null $city
 * @property string $phone
 * @property string $email
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Appointment> $appointments
 * @property-read int|null $appointments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Campaign> $campaigns
 * @property-read int|null $campaigns_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Donation> $donations
 * @property-read int|null $donations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BloodCenter whereUpdatedAt($value)
 */
	class BloodCenter extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $start_date
 * @property string $end_date
 * @property int $blood_center_id
 * @property string|null $location
 * @property string|null $image_path
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BloodCenter $bloodCenter
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereBloodCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campaign whereUpdatedAt($value)
 */
	class Campaign extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $blood_center_id
 * @property int|null $appointment_id
 * @property string $blood_group
 * @property int $volume_ml
 * @property string $donation_date
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Appointment|null $appointment
 * @property-read \App\Models\BloodCenter $bloodCenter
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereAppointmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereBloodCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereBloodGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereDonationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Donation whereVolumeMl($value)
 */
	class Donation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $phone_number
 * @property string|null $blood_group
 * @property string|null $gender
 * @property string|null $date_of_birth
 * @property string|null $address
 * @property string $role
 * @property int $is_active
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Appointment> $appointments
 * @property-read int|null $appointments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Donation> $donations
 * @property-read int|null $donations_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBloodGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

