# Public Website Pages

The public website is defined in `routes/web.php`.

It uses Blade views in `resources/views`.

The shared website layout is `resources/views/layouts/app.blade.php`.

## Shared Layout

Every public page uses the same layout.

The layout includes:

- NBTS logo.
- Desktop navigation.
- Mobile navigation.
- Footer.
- Links to important pages.
- Download app button.

The navigation links are:

- Home
- About
- Blood Centers
- Campaigns
- Real-Time Impact
- Can I Donate?
- Download Mobile App

The footer includes:

- About NBTS link.
- Find Blood Centers link.
- Active Campaigns link.
- National Impact link.
- Mobile Application link.
- Legal placeholder links.
- App download links.

## Home Page

Route: `/`

Controller: `HomeController@index`

View: `resources/views/welcome.blade.php`

The home page is the main landing page.

It shows:

- A public introduction to NBTS.
- Donation impact statistics.
- Number of donors.
- Number of donations.
- Estimated lives saved.
- Active campaigns.
- Links that guide users to the mobile app, campaigns, and centers.

The page gets real data from:

- Users with donor role.
- Completed donations.
- Active campaigns.

Some numbers include added demo values so the page looks active even when the database has little data.

## About Page

Route: `/about`

View: `resources/views/web/about.blade.php`

The about page explains the mission of NBTS.

It shows:

- The mission message.
- Main values: reliability, accessibility, and community.
- The donation process.
- A final call to download the app.

The donation process shown is:

1. Registration.
2. Health check.
3. Donation.
4. Refreshment.

## Download App Page

Route: `/download-app`

View: `resources/views/web/download.blade.php`

This page tells users to download the NBTS mobile app.

It shows:

- A phone mockup.
- Google Play button.
- App Store button.
- QR code area placeholder.
- Main app benefits.

The page says the app lets donors:

- Book appointments.
- Track donation history.
- Find blood drives.
- Use a personal donation portal.

The store links are placeholders in the current code.

## Eligibility Page

Route: `/eligibility`

Controller: `EligibilityCheckerController@index`

View: `resources/views/web/eligibility.blade.php`

This page gives public information about who can donate.

It is a public guidance page, not the protected staff eligibility check.

It helps visitors understand basic donation requirements before they use the app or visit a center.

The real donor eligibility logic is handled by `EligibilityService` in the backend.

## Analytics Page

Route: `/analytics`

Controller: `AnalyticsController@index`

View: `resources/views/web/analytics.blade.php`

This page is a public impact dashboard.

It shows:

- Total donations.
- Estimated lives impacted.
- Blood collected in liters.
- Active campaigns.
- Available blood units.
- Low stock blood groups.
- Donation trend chart.
- Blood group distribution chart.

The page uses ApexCharts in the browser.

The page gets data from:

- `donations`
- `campaigns`
- `blood_units`
- `blood_inventory`

## Blood Centers List Page

Route: `/centers`

Controller: `BloodCenterDirectoryController@index`

View: `resources/views/web/centers/index.blade.php`

This page lists active blood centers.

Users can:

- See center cards.
- Search by center name.
- Search by address.
- Open a center detail page.
- Go to the download app page to book.

The page only lists centers marked active by the controller query.

The results are paginated with 9 centers per page.

## Blood Center Detail Page

Route: `/centers/{center}`

Controller: `BloodCenterDirectoryController@show`

View: `resources/views/web/centers/show.blade.php`

This page shows one blood center.

It shows:

- Center name.
- Center status.
- Address.
- Contact phone.
- Generated email based on center name.
- Operating hours.
- Donation requirements.
- Download app button for booking.
- More active centers.

The operating hours and requirements are static text in the view.

The booking action sends users to the mobile app download page.

## Campaigns List Page

Route: `/campaigns`

Controller: `CampaignDirectoryController@index`

View: `resources/views/web/campaigns/index.blade.php`

This page lists blood donation campaigns.

It shows campaign cards with related blood center information.

Users can:

- Browse campaigns.
- Open a campaign detail page.
- Go to the app download page to participate.

The controller loads campaigns with their blood center and paginates them.

## Campaign Detail Page

Route: `/campaigns/{campaign}`

Controller: `CampaignDirectoryController@show`

View: `resources/views/web/campaigns/show.blade.php`

This page shows one campaign.

It explains the campaign and encourages users to join through the mobile app.

It can show:

- Campaign title.
- Campaign status.
- Campaign location.
- Related blood center.
- Start and end dates.
- Campaign description.
- Target details.
- Download app call to action.

## Admin Login Page

Route: `/admin`

Provider: `AdminPanelProvider`

The admin panel is not part of `routes/web.php`. Filament registers it at `/admin`.

Only active users with allowed roles can access it.

Allowed roles are:

- `super_admin`
- `nbts_admin`
- `center_manager`
- `center_staff`

