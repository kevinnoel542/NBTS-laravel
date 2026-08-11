<?php

use App\PermissionName;

return [
    'workspaces' => [
        'donor-reception' => [
            'title' => 'console.workspaces.donor_reception.title',
            'description' => 'console.workspaces.donor_reception.description',
            'icon' => 'scan-line',
            'group' => 'workflow',
            'permissions' => [PermissionName::ViewDonors->value],
            'tabs' => ['search', 'scan', 'registration', 'profile'],
        ],
        'appointments' => [
            'title' => 'console.workspaces.appointments.title',
            'description' => 'console.workspaces.appointments.description',
            'icon' => 'calendar-clock',
            'group' => 'workflow',
            'permissions' => [PermissionName::ViewAppointments->value],
            'tabs' => ['today', 'upcoming', 'pending', 'check_in'],
        ],
        'eligibility' => [
            'title' => 'console.workspaces.eligibility.title',
            'description' => 'console.workspaces.eligibility.description',
            'icon' => 'clipboard-check',
            'group' => 'workflow',
            'permissions' => [PermissionName::CheckEligibility->value],
            'tabs' => ['screening_queue', 'deferrals', 'history'],
        ],
        'donations' => [
            'title' => 'console.workspaces.donations.title',
            'description' => 'console.workspaces.donations.description',
            'icon' => 'droplets',
            'group' => 'workflow',
            'permissions' => [PermissionName::ViewDonations->value],
            'tabs' => ['record', 'verify_blood_group', 'history'],
        ],
        'blood-operations' => [
            'title' => 'console.workspaces.blood_operations.title',
            'description' => 'console.workspaces.blood_operations.description',
            'icon' => 'test-tubes',
            'group' => 'workflow',
            'permissions' => [PermissionName::ViewInventory->value],
            'tabs' => ['testing_queue', 'blood_units', 'inventory', 'transfers', 'expiry', 'disposal'],
        ],
        'response' => [
            'title' => 'console.workspaces.response.title',
            'description' => 'console.workspaces.response.description',
            'icon' => 'siren',
            'group' => 'coordination',
            'permissions' => [PermissionName::ViewCampaigns->value, PermissionName::ManageNotifications->value],
            'tabs' => ['low_stock_alerts', 'campaigns', 'donor_communication'],
        ],
        'engagement' => [
            'title' => 'console.workspaces.engagement.title',
            'description' => 'console.workspaces.engagement.description',
            'icon' => 'heart-handshake',
            'group' => 'coordination',
            'permissions' => [PermissionName::ManageNotifications->value, PermissionName::ManageLoyalty->value],
            'tabs' => ['notifications', 'deliveries', 'loyalty', 'rewards', 'leaderboard'],
        ],
        'content' => [
            'title' => 'console.workspaces.content.title',
            'description' => 'console.workspaces.content.description',
            'icon' => 'newspaper',
            'group' => 'coordination',
            'permissions' => [PermissionName::ViewArticles->value],
            'tabs' => ['news', 'publications', 'faqs', 'schedules', 'public_pages'],
        ],
        'intelligence' => [
            'title' => 'console.workspaces.intelligence.title',
            'description' => 'console.workspaces.intelligence.description',
            'icon' => 'chart-no-axes-combined',
            'group' => 'coordination',
            'permissions' => [PermissionName::ViewReports->value],
            'tabs' => ['reports', 'analytics', 'exports'],
        ],
        'administration' => [
            'title' => 'console.workspaces.administration.title',
            'description' => 'console.workspaces.administration.description',
            'icon' => 'shield',
            'group' => 'system',
            'permissions' => [
                PermissionName::ViewUsers->value,
                PermissionName::ManageCenters->value,
                PermissionName::ViewAudits->value,
                PermissionName::ManageSettings->value,
                PermissionName::ManageBackups->value,
            ],
            'tabs' => ['users', 'roles_permissions', 'centers', 'settings', 'audit', 'recovery'],
        ],
    ],
];
