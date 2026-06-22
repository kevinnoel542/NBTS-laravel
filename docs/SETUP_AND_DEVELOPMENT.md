# Setup And Development

This project is a Laravel application.

## Main Requirements

- PHP 8.2 or newer
- Composer
- Node.js and npm
- A database supported by Laravel

## Important Packages

The backend uses:

- Laravel 12
- Laravel Sanctum
- Filament 3
- Spatie Laravel Permission
- Predis

The development tools include:

- Laravel Pint
- PHPUnit
- Laravel Sail
- Laravel Pail
- Faker

## Install Dependencies

Run:

```bash
composer install
npm install
```

## Environment File

Create `.env` from `.env.example` if needed:

```bash
cp .env.example .env
```

Then generate the app key:

```bash
php artisan key:generate
```

Update the database settings in `.env`.

## Run Migrations

Run:

```bash
php artisan migrate
```

## Seed Starting Data

Run:

```bash
php artisan db:seed
```

The seeders include roles, permissions, blood centers, loyalty data, and demo data.

## Build Frontend Assets

For development:

```bash
npm run dev
```

For production build:

```bash
npm run build
```

## Run The App

Run:

```bash
php artisan serve
```

Then open the Laravel URL shown in the terminal.

Usually it is:

`http://127.0.0.1:8000`

## Admin Panel

Admin panel path:

`/admin`

Only active users with allowed roles can log in.

Allowed roles:

- `super_admin`
- `nbts_admin`
- `center_manager`
- `center_staff`

## API Base URL

API base path:

`/api/v1`

Example:

`/api/v1/ping`

## Useful Composer Scripts

The project has these scripts in `composer.json`:

### Setup

```bash
composer run setup
```

This installs Composer dependencies, creates `.env` if needed, generates the key, runs migrations, installs npm packages, and builds frontend assets.

### Development

```bash
composer run dev
```

This runs the server, queue listener, logs, and Vite together.

### Tests

```bash
composer run test
```

This clears config and runs Laravel tests.

## Tests

Test files are in:

- `tests/Unit`
- `tests/Feature`

Current test files are the default example tests.

Run tests with:

```bash
php artisan test
```

## Main Code Areas

- Routes: `routes/web.php` and `routes/api.php`
- Web controllers: `app/Http/Controllers/Web`
- API controllers: `app/Http/Controllers/Api`
- Staff API controllers: `app/Http/Controllers/Api/Staff`
- Models: `app/Models`
- Services: `app/Services`
- Admin resources: `app/Filament/Resources`
- Public pages: `resources/views`
- Database migrations: `database/migrations`
- Seeders: `database/seeders`

## Notes For Future Developers

- Put business rules in services when possible.
- Keep API controllers focused on validation and responses.
- Keep database relationships in models.
- Use roles and permissions for staff actions.
- Be careful when changing donation, inventory, and eligibility logic because they affect each other.
- When a completed donation is recorded, inventory and loyalty are also updated.
- When inventory changes, low stock alerts may also change.

