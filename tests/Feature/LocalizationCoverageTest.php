<?php

use App\AppointmentStatus;
use App\ArticleStatus;
use App\BloodUnitStatus;
use App\CampaignStatus;
use App\CampaignType;
use App\DeferralType;
use App\DonationStatus;
use App\DonationType;
use App\EligibilityStatus;
use App\LowStockAlertStatus;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

test('every English translation key and placeholder has a Kiswahili equivalent', function () {
    foreach ([
        'api',
        'auth',
        'console',
        'pagination',
        'passwords',
        'pdf',
        'public',
        'operations',
        'system',
        'validation',
    ] as $filename) {
        $english = Arr::dot(require lang_path('en/'.$filename.'.php'));
        $swahili = Arr::dot(require lang_path('sw/'.$filename.'.php'));

        $englishStringKeys = array_keys(array_filter($english, is_string(...)));

        expect(array_values(array_diff($englishStringKeys, array_keys($swahili))))
            ->toBe([], 'Missing Kiswahili keys in '.$filename.'.php');

        foreach ($english as $key => $englishValue) {
            if (! is_string($englishValue)) {
                continue;
            }

            preg_match_all('/:[a-z_]+/i', $englishValue, $englishPlaceholders);
            preg_match_all('/:[a-z_]+/i', (string) $swahili[$key], $swahiliPlaceholders);

            $englishPlaceholders[0] = array_values(array_unique($englishPlaceholders[0]));
            $swahiliPlaceholders[0] = array_values(array_unique($swahiliPlaceholders[0]));
            sort($englishPlaceholders[0]);
            sort($swahiliPlaceholders[0]);

            expect($swahiliPlaceholders[0])->toBe(
                $englishPlaceholders[0],
                'Placeholder mismatch for '.$filename.'.'.$key,
            );
        }
    }
});

test('all literal interface strings have Kiswahili JSON translations', function () {
    $translations = json_decode(
        File::get(lang_path('sw.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $literalKeys = [];

    foreach ([
        ...File::allFiles(app_path()),
        ...File::allFiles(resource_path('views')),
        ...File::allFiles(base_path('vendor/livewire/flux/stubs/resources/views')),
    ] as $file) {
        if (! in_array($file->getExtension(), ['php'], true)) {
            continue;
        }

        preg_match_all(
            '/__\(\s*([\'\"])(.*?)\1\s*(?:,|\))/s',
            File::get($file->getPathname()),
            $matches,
        );

        foreach ($matches[2] as $key) {
            if (preg_match('/^(api|auth|console|operations|pagination|passwords|pdf|public|system|validation)\./', $key) === 1) {
                continue;
            }

            $literalKeys[] = $key;
        }
    }

    $missingKeys = array_values(array_diff(array_unique($literalKeys), array_keys($translations)));
    sort($missingKeys);

    expect($missingKeys)->toBe([]);
});

test('operational status codes have labels in both locales', function () {
    $statusCodes = collect([
        ...AppointmentStatus::cases(),
        ...ArticleStatus::cases(),
        ...BloodUnitStatus::cases(),
        ...CampaignStatus::cases(),
        ...DonationStatus::cases(),
        ...EligibilityStatus::cases(),
        ...LowStockAlertStatus::cases(),
    ])->pluck('value')->unique();
    $typeCodes = collect([
        ...CampaignType::cases(),
        ...DeferralType::cases(),
        ...DonationType::cases(),
    ])->pluck('value')->unique();

    foreach (['en', 'sw'] as $locale) {
        App::setLocale($locale);

        foreach ($statusCodes as $statusCode) {
            expect(__('operations.status.'.$statusCode))->not->toBe('operations.status.'.$statusCode);
        }

        foreach ($typeCodes as $typeCode) {
            expect(__('operations.types.'.$typeCode))->not->toBe('operations.types.'.$typeCode);
        }
    }
});

test('managed bilingual content fields and fallback policy are explicit', function () {
    expect(config('content.locales'))->toBe(['en', 'sw'])
        ->and(config('content.base_locale'))->toBe('en')
        ->and(config('content.storage_strategy'))->toBe('polymorphic_translation_records')
        ->and(config('content.publish_requires_base_locale'))->toBeTrue()
        ->and(config('content.machine_translation_enabled'))->toBeFalse()
        ->and(config('content.managed_fields.articles'))->toContain('title', 'summary', 'body', 'meta_description')
        ->and(config('content.managed_fields.campaigns'))->toContain('title', 'description', 'location')
        ->and(config('content.managed_fields.blood_centers'))->toContain('name', 'address', 'opening_hours', 'services')
        ->and(config('content.notification_strategy'))->toBe('render_in_recipient_locale_on_send');
});

test('validation and system feedback are rendered in Kiswahili', function () {
    App::setLocale('sw');

    $validator = Validator::make([], [
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    expect($validator->errors()->first('email'))->toBe('Sehemu ya barua pepe inahitajika.')
        ->and($validator->errors()->first('password'))->toBe('Sehemu ya nenosiri inahitajika.')
        ->and(__('system.search_empty'))->toBe('Hakuna rekodi zinazolingana na utafutaji au vichujio vyako.')
        ->and(__('pdf.inventory_report'))->toBe('Ripoti ya akiba ya damu');
});

test('guest and staff account screens render in English and Kiswahili', function () {
    $this->withSession(['locale' => 'en'])
        ->get(route('login'))
        ->assertOk()
        ->assertSee('Log in to your account')
        ->assertSee('Kiswahili');

    $this->withSession(['locale' => 'sw'])
        ->get(route('login'))
        ->assertOk()
        ->assertSee('Ingia kwenye akaunti yako')
        ->assertSee('Kiingereza')
        ->assertDontSee('Log in to your account');

    $staff = User::factory()->staff()->create(['locale' => 'sw']);

    $this->actingAs($staff)
        ->withSession([
            'locale' => 'sw',
            'auth.password_confirmed_at' => time(),
        ])
        ->get(route('security.edit'))
        ->assertOk()
        ->assertSee('Mipangilio ya usalama')
        ->assertSee('Sasisha nenosiri')
        ->assertSee('Funguo za kuingia')
        ->assertSee('Kiingereza')
        ->assertDontSee('Security settings');
});
