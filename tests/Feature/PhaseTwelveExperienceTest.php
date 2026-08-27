<?php

use App\BloodGroup;
use App\Models\BloodCenter;
use App\Models\DocumentSnapshot;
use App\Models\DonorProfile;
use App\Models\KpiDefinition;
use App\Models\NotificationOutbox;
use App\Models\ReportSnapshot;
use App\Models\User;
use App\RoleName;
use App\Services\PhaseTwelveExperienceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('notification outbox enforces idempotency consent preferences quiet hours and non coercive recognition', function () {
    $this->travelTo('2026-08-27 22:15:00');

    $center = BloodCenter::factory()->create();
    $actor = phaseTwelveActor(RoleName::NationalDonorEngagementContentOfficer);
    $recipient = User::factory()->donor()->create([
        'blood_group' => BloodGroup::OPositive,
        'locale' => 'sw',
    ]);
    DonorProfile::factory()->for($recipient)->create([
        'consented_at' => now()->subMonth(),
        'preferred_center_id' => $center->id,
        'sms_reminders_enabled' => true,
        'language' => 'sw',
    ]);
    $recipient->userNotifications()->create([
        'body' => 'Previous safe response',
        'data' => ['response' => 'responded'],
        'title' => 'Previous response',
        'type' => 'campaign',
    ]);

    $service = app(PhaseTwelveExperienceService::class);
    $criteria = [
        'blood_group' => BloodGroup::OPositive->value,
        'channel' => 'sms',
        'consented' => true,
        'eligible_on_or_before' => today()->toDateString(),
        'language' => 'sw',
        'location_id' => $center->id,
        'previous_response' => 'responded',
    ];

    expect($service->segmentDonorAudience($actor, $criteria))->toContain($recipient->id);

    expect(fn () => $service->segmentDonorAudience($actor, ['diagnosis' => 'sensitive']))
        ->toThrow(ValidationException::class);

    $outbox = $service->queueNotificationOutbox(
        actor: $actor,
        recipient: $recipient,
        templateCode: 'appointment_reminder_v1',
        alertType: 'appointment',
        channel: 'sms',
        locale: 'sw',
        idempotencyKey: 'phase-12-idempotent-message',
        segmentCriteria: $criteria,
        preferences: ['sms' => true, 'push' => true, 'email' => false],
        consent: ['sms' => true, 'push' => true, 'email' => false],
        quietHours: ['enabled' => true, 'start' => '21:00', 'end' => '07:00'],
        payload: ['title' => 'Kumbusho la miadi', 'body' => 'Tafadhali hudhuria muda wako uliopangwa.'],
    );
    $duplicate = $service->queueNotificationOutbox(
        actor: $actor,
        recipient: $recipient,
        templateCode: 'appointment_reminder_v1',
        alertType: 'appointment',
        channel: 'sms',
        locale: 'sw',
        idempotencyKey: 'phase-12-idempotent-message',
        segmentCriteria: $criteria,
        preferences: ['sms' => true],
        consent: ['sms' => true],
        quietHours: ['enabled' => true, 'start' => '21:00', 'end' => '07:00'],
        payload: ['title' => 'Kumbusho la miadi', 'body' => 'Tafadhali hudhuria muda wako uliopangwa.'],
    );

    expect($outbox)->toBeInstanceOf(NotificationOutbox::class)
        ->and($duplicate->id)->toBe($outbox->id)
        ->and($outbox->status)->toBe('deferred')
        ->and($outbox->after_commit)->toBeTrue()
        ->and($outbox->non_coercive)->toBeTrue()
        ->and($outbox->recipient_hash)->not->toBeNull()
        ->and($outbox->segment_criteria)->not->toHaveKey('diagnosis')
        ->and($outbox->quiet_hours['enabled'])->toBeTrue();

    $suppressed = $service->queueNotificationOutbox(
        actor: $actor,
        recipient: $recipient,
        templateCode: 'email_campaign_v1',
        alertType: 'campaign',
        channel: 'email',
        locale: 'en',
        idempotencyKey: 'phase-12-suppressed-message',
        segmentCriteria: ['channel' => 'email', 'consented' => true],
        preferences: ['email' => false],
        consent: ['email' => false],
        quietHours: ['enabled' => false],
        payload: ['title' => 'Campaign', 'body' => 'Safe donor information.'],
    );

    expect($suppressed->status)->toBe('suppressed')
        ->and($suppressed->last_error)->toContain('preference');

    expect(fn () => $service->queueNotificationOutbox(
        actor: $actor,
        recipient: $recipient,
        templateCode: 'recognition_v1',
        alertType: 'recognition',
        channel: 'sms',
        locale: 'en',
        idempotencyKey: 'phase-12-payment-message',
        segmentCriteria: ['channel' => 'sms'],
        preferences: ['sms' => true],
        consent: ['sms' => true],
        quietHours: ['enabled' => false],
        payload: ['title' => 'Reward', 'body' => 'Unsafe', 'recognition' => 'payment'],
    ))->toThrow(ValidationException::class);
});

test('kpi dictionary and reports require approved definitions balanced metrics and reconciliation', function () {
    $actor = phaseTwelveActor(RoleName::SuperAdmin);
    $service = app(PhaseTwelveExperienceService::class);

    expect(fn () => $service->approveKpiDefinition(
        actor: $actor,
        code: 'KPI-unsafe',
        name: 'Unsafe metric',
        category: 'collection',
        numerator: 'Only collection count',
        denominator: 'All visits',
        exclusions: [],
        sourceModels: [],
        owner: 'Operations',
        frequency: 'monthly',
        target: null,
        dataQualityChecks: ['period_closed'],
        antiGamingControls: ['none'],
    ))->toThrow(ValidationException::class);

    $kpi = $service->approveKpiDefinition(
        actor: $actor,
        code: 'KPI-P12-CONVERSION',
        name: 'Donor conversion rate',
        category: 'donor',
        numerator: 'Completed donations from eligible checked-in donors',
        denominator: 'Eligible checked-in donors',
        exclusions: ['cancelled_before_check_in'],
        sourceModels: ['appointments', 'eligibility_records', 'donations'],
        owner: 'National operations',
        frequency: 'monthly',
        target: '>= 65%',
        dataQualityChecks: ['center_scope_present', 'period_closed', 'duplicate_reviewed'],
        antiGamingControls: ['balance_with_deferrals', 'balance_with_reactions', 'balance_with_wastage'],
    );

    expect(fn () => $service->generateReportSnapshot(
        actor: $actor,
        kpiDefinition: $kpi,
        reportType: 'national safety summary',
        periodStart: '2026-08-01',
        periodEnd: '2026-08-31',
        scope: ['level' => 'national'],
        metrics: ['collection_total' => 10],
        reconciliation: ['source_total' => 10, 'reported_total' => 9],
    ))->toThrow(ValidationException::class);

    $report = $service->generateReportSnapshot(
        actor: $actor,
        kpiDefinition: $kpi,
        reportType: 'national safety summary',
        periodStart: '2026-08-01',
        periodEnd: '2026-08-31',
        scope: ['level' => 'national'],
        metrics: [
            'collection_total' => 100,
            'safety_total' => 100,
            'utilization_total' => 82,
            'wastage_total' => 4,
            'adverse_event_total' => 1,
        ],
        reconciliation: ['source_total' => 100, 'reported_total' => 100],
        nationalDashboardReady: true,
    );

    expect($kpi)->toBeInstanceOf(KpiDefinition::class)
        ->and($kpi->status)->toBe('approved')
        ->and($kpi->anti_gaming_controls)->toContain('balance_with_wastage')
        ->and($report)->toBeInstanceOf(ReportSnapshot::class)
        ->and($report->deidentified)->toBeTrue()
        ->and($report->national_dashboard_ready)->toBeTrue()
        ->and($report->reconciliation['source_total'])->toBe(100);
});

test('document snapshots and large exports are authorized localized audited encrypted and expiring', function () {
    $actor = phaseTwelveActor(RoleName::SuperAdmin);
    $service = app(PhaseTwelveExperienceService::class);

    $document = $service->createDocumentSnapshot(
        actor: $actor,
        documentType: 'donor summary',
        locale: 'sw',
        stableIdentifiers: ['donor_id' => 'DNR-P12-001', 'document_id' => 'DOC-P12-001'],
        labels: ['title' => 'Muhtasari wa mchangiaji', 'issued_at' => 'Imetolewa'],
        accessScope: ['permission' => 'reports.export', 'center_scope' => true],
        verificationContext: ['source_period' => '2026-08', 'source' => 'authoritative_records', 'version' => 1],
        snapshotPayload: ['donor' => 'privacy-safe snapshot', 'medical_detail' => 'excluded'],
        largeExport: true,
        periodStart: '2026-08-01',
        periodEnd: '2026-08-31',
    );

    expect($document)->toBeInstanceOf(DocumentSnapshot::class)
        ->and($document->authorized)->toBeTrue()
        ->and($document->audited)->toBeTrue()
        ->and($document->locale)->toBe('sw')
        ->and($document->queued)->toBeTrue()
        ->and($document->queue_name)->toBe('exports')
        ->and($document->large_export)->toBeTrue()
        ->and($document->expires_at)->not->toBeNull()
        ->and($document->checksum)->toHaveLength(64)
        ->and($document->encrypted_snapshot_payload['donor'])->toBe('privacy-safe snapshot')
        ->and($document->getRawOriginal('encrypted_snapshot_payload'))->not->toContain('privacy-safe snapshot');
});

test('phase twelve staff workspaces and public legal pages render localized foundations', function () {
    $administrator = phaseTwelveActor(RoleName::SuperAdmin);

    foreach (['laboratory', 'components', 'inventory', 'logistics', 'hospital', 'quality'] as $workspace) {
        $this->actingAs($administrator)
            ->get(route('operations.workspace', ['workspace' => $workspace]))
            ->assertOk()
            ->assertSee(__('console.workspaces.'.str_replace('-', '_', $workspace).'.title'))
            ->assertSee(__('console.common.search'))
            ->assertSee(__('console.common.clear_filters'));
    }

    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee('Privacy policy')
        ->assertSee('Public by design. Private by default.');

    $swahiliUser = User::factory()->donor()->create(['locale' => 'sw']);

    $this->actingAs($swahiliUser)
        ->get(route('data-protection'))
        ->assertOk()
        ->assertSee('Taarifa ya ulinzi wa data')
        ->assertSee('Exports ni ushahidi unaodhibitiwa');

    $donor = User::factory()->donor()->create();
    $this->actingAs($donor)
        ->get(route('operations.workspace', ['workspace' => 'hospital']))
        ->assertForbidden();
});

function phaseTwelveActor(RoleName $role): User
{
    $user = User::factory()->staff()->create();
    $user->syncRoles([$role->value]);

    return $user;
}
