<?php

use App\Http\Resources\Api\V1\AppointmentResource;
use App\Http\Resources\Api\V1\ArticleResource;
use App\Http\Resources\Api\V1\CampaignResource;
use App\Http\Resources\Api\V1\DonationResource;
use App\Http\Resources\Api\V1\DonationScheduleResource;
use App\Http\Resources\Api\V1\EligibilityResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Appointment;
use App\Models\Article;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\DonorProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

test('API state and type codes remain stable while display labels follow the locale', function () {
    $appointment = Appointment::factory()->create()->load('bloodCenter');
    $article = Article::factory()->create();
    $campaign = Campaign::factory()->create()->load('bloodCenter');
    $donation = Donation::factory()->create()->load('bloodCenter');
    $donor = User::factory()->donor()->create();
    DonorProfile::factory()->create(['user_id' => $donor]);
    $donor->load('donorProfile.preferredCenter');
    $eligibility = [
        'status' => 'temporarily_deferred',
        'eligible' => false,
        'message' => 'Localized guidance',
        'reasons' => ['Localized reason'],
        'next_eligible_donation_date' => '2026-10-01',
        'last_eligibility_checked_at' => '2026-08-01T09:00:00+03:00',
        'clinical_screening_required' => true,
    ];
    $request = Request::create('/api/v1/test', 'GET');

    $resolve = function (string $locale) use (
        $appointment,
        $article,
        $campaign,
        $donation,
        $donor,
        $eligibility,
        $request,
    ): array {
        App::setLocale($locale);

        return [
            'appointment' => (new AppointmentResource($appointment))->resolve($request),
            'article' => (new ArticleResource($article))->resolve($request),
            'campaign' => (new CampaignResource($campaign))->resolve($request),
            'donation' => (new DonationResource($donation))->resolve($request),
            'schedule' => (new DonationScheduleResource($campaign))->resolve($request),
            'eligibility' => (new EligibilityResource($eligibility))->resolve($request),
            'user' => (new UserResource($donor))->resolve($request),
        ];
    };

    $english = $resolve('en');
    $swahili = $resolve('sw');

    foreach ([
        ['appointment', 'status'],
        ['article', 'status'],
        ['campaign', 'status'],
        ['campaign', 'type'],
        ['campaign', 'blood_group'],
        ['donation', 'status'],
        ['donation', 'donation_type'],
        ['donation', 'blood_group'],
        ['schedule', 'status'],
        ['schedule', 'type'],
        ['eligibility', 'status'],
    ] as [$resource, $field]) {
        expect($swahili[$resource][$field])->toBe($english[$resource][$field]);
    }

    expect($swahili['user']['donor_profile']['blood_group_status'])
        ->toBe($english['user']['donor_profile']['blood_group_status'])
        ->and($swahili['user']['donor_profile']['eligibility_status'])
        ->toBe($english['user']['donor_profile']['eligibility_status'])
        ->and($swahili['appointment']['status_label'])->not->toBe($english['appointment']['status_label'])
        ->and($swahili['campaign']['type_label'])->not->toBe($english['campaign']['type_label'])
        ->and($swahili['donation']['status_label'])->not->toBe($english['donation']['status_label'])
        ->and($swahili['eligibility']['status_label'])->not->toBe($english['eligibility']['status_label']);
});
