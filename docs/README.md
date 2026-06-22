# NBTS Project Documentation

This folder explains the NBTS blood donation platform in simple English.

NBTS means National Blood Transfusion Service.

The project is a Laravel web and API system for blood donation work. It helps donors find blood centers, see campaigns, check if they may donate, book appointments through the mobile app, and track their donation history. It also helps staff manage donors, appointments, donations, blood stock, reports, and low stock alerts.

## Documentation Files

- [Project Overview](PROJECT_OVERVIEW.md): What the system does and who uses it.
- [Public Website Pages](PUBLIC_WEBSITE_PAGES.md): Every public page and what it shows.
- [Admin Panel Pages](ADMIN_PANEL_PAGES.md): Every admin dashboard page and what staff use it for.
- [Mobile App API](MOBILE_APP_API.md): The API routes used by the mobile app and staff tools.
- [Data And Database](DATA_AND_DATABASE.md): The main database records and how they connect.
- [Main Workflows](MAIN_WORKFLOWS.md): Step by step explanation of the important actions.
- [Setup And Development](SETUP_AND_DEVELOPMENT.md): How the project is built and how to run it.
- [Known Implementation Notes](KNOWN_IMPLEMENTATION_NOTES.md): Important code mismatches and things to check.

## Main Parts Of The Project

The project has four main parts:

1. Public website
2. Mobile app API
3. Staff API
4. Admin dashboard

The public website is for visitors and donors.

The mobile app API is for the donor mobile app.

The staff API is for staff actions like confirming appointments, checking donors, recording donations, and managing stock.

The admin dashboard is built with Filament. It gives approved staff a browser interface for managing records.

## Technology Used

- Laravel 12
- PHP 8.2 or newer
- Laravel Sanctum for API login tokens
- Filament 3 for the admin panel
- Spatie Laravel Permission for roles and permissions
- Vite, Tailwind CSS, and Blade for the public website
- ApexCharts on the analytics page
