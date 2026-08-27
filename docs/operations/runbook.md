# NBTS operations runbook

Last verified: 2026-08-01

## Scope and ownership

This runbook covers the safety controls that are implemented in NBTS-NEW today. It does not claim that off-site production recovery is complete.

- Service owner: the NBTS ICT/system-administration function.
- Backup operator: a named active `super_admin` account with `backups.manage`; the human name and ticket/change reference must be recorded in the private operations log for every manual run.
- Restore approver: a different NBTS ICT lead or designated incident lead.
- Application verifier: an NBTS operations representative who can reconcile donor, appointment, donation, unit, and inventory totals.
- Credentials, database login paths, archive passwords, Firebase service accounts, and session material must remain outside Git.

## Environment boundaries

| Environment | Database | Permitted use |
| --- | --- | --- |
| Previous/shared application | `nbts` | Read-only migration source until an approved cutover window. |
| NBTS-NEW development | `nbts_new_dev` | Local development and browser QA. |
| Automated tests | `nbts_new_test` | Disposable test state only. |
| Restore drill | `nbts_restore_YYYYMMDD_HHMM` | Isolated verification; never a live application target. |

Before a migration, backup, or restore, verify the active environment and database without printing credentials:

```bash
php artisan about --only=environment
php artisan config:show database.default
php artisan tinker --execute 'dump(DB::selectOne("select database() as name")->name);'
```

Never run `migrate:fresh`, destructive seeders, or a restore against `nbts` or a production database.

## Verified foundation backup

The initial source backup is private and Git-ignored at:

```text
storage/app/private/NBTS/2026-08-01-09-19-58.zip
```

Evidence recorded on 2026-08-01:

- The archive is 15,968 bytes, mode `0600`, and contains `db-dumps/mysql-nbts.sql`.
- `unzip -t` reports no compressed-data errors.
- The archive restored into an isolated database with 36 tables and no missing table names.
- Source and restore counts matched for 11 users, 7 donor profiles, 7 donations, 4 blood centers, 10 appointments, and 23 migration records.
- The archive contains database data only. It is a private local development artifact, not an encrypted/off-site production backup.

The archive was moved from the package's former `Laravel/` destination to `NBTS/` after the application name was corrected, so `php artisan backup:list` now monitors it under the current application name.

## Creating and checking a local database backup

1. Confirm the configured database is the approved source.
2. Confirm `storage/app/private` is not publicly served and the operator's umask/file permissions are restrictive.
3. Run the database-only backup without notification noise in local development:

```bash
php artisan backup:run --only-db --isolated --disable-notifications --no-interaction
php artisan backup:list
php artisan backup:monitor
```

4. Test the exact new archive before relying on it:

```bash
unzip -t storage/app/private/NBTS/<timestamp>.zip
unzip -l storage/app/private/NBTS/<timestamp>.zip
```

5. Restrict a manually created local archive if the host umask did not:

```bash
chmod 600 storage/app/private/NBTS/<timestamp>.zip
```

6. Record the source database, archive filename, SHA-256 checksum, operator, start/end time, result, and change/incident reference in the private operations log.

The configured local retention policy keeps all backups for 7 days, daily backups for 16 days, weekly backups for 8 weeks, monthly backups for 4 months, yearly backups for 2 years, and caps local storage at 5,000 MB. `php artisan backup:clean` applies that policy and always retains the newest archive.

## Isolated restore drill

Use a MySQL client login path or interactive password prompt. Never put a password in a command, shell history, documentation, or ticket.

```bash
ARCHIVE_PATH='storage/app/private/NBTS/<timestamp>.zip'
RESTORE_DATABASE='nbts_restore_YYYYMMDD_HHMM'

mysql --login-path=nbts-operator --execute="CREATE DATABASE \`${RESTORE_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
unzip -p "$ARCHIVE_PATH" 'db-dumps/*.sql' | mysql --login-path=nbts-operator "$RESTORE_DATABASE"
mysql --login-path=nbts-operator "$RESTORE_DATABASE" --execute='SHOW TABLES'
```

Verification must include:

- archive integrity and expected dump member;
- migration count and table-name comparison;
- critical counts for users, donor profiles, appointments, eligibility/deferrals, donations, blood units, inventory, campaigns, notifications, and audit logs;
- foreign-key/index comparison where schema reconciliation is being proved;
- application smoke tests against a temporary environment explicitly pointed at the restore database;
- removal of the temporary database only after the drill evidence is accepted.

Dropping the isolated restore database is a destructive cleanup step. Resolve and record the exact `nbts_restore_...` target first, obtain the restore operator's confirmation, and never use a wildcard or unresolved variable.

## Deployment baseline

Required runtime: PHP 8.4, a supported MySQL 8 release, Composer 2, Node.js 22 for asset builds, a queue worker, and a one-minute scheduler trigger.

1. Take and verify a pre-deployment backup.
2. Put the service into the approved maintenance/write-freeze state when a data migration requires it.
3. Install locked dependencies and build assets:

```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build
```

4. Run only reviewed additive migrations:

```bash
php artisan migrate --force --no-interaction
php artisan storage:link --no-interaction
php artisan optimize
php artisan queue:restart
```

5. Ensure the scheduler runs `php artisan schedule:run` every minute and queue workers are supervised.
6. Run route, authentication, public-page, API, queue, storage, cache, and database smoke checks.
7. Compare the approved pre/post record counts and monitor application logs, failed jobs, authentication failures, Firebase delivery, and inventory exceptions.

Production environment values, queue/mail/SMS/Firebase credentials, off-site backup disks, archive encryption, alert recipients, recovery objectives, and final deployment platform remain release decisions. They must be configured and proven before cutover.

## Incident and rollback baseline

- Stop or freeze writes before restoring data.
- Preserve the failed state and logs for diagnosis.
- Prefer a forward fix when data integrity is intact; never reverse an additive migration blindly.
- A rollback must use the last verified backup and the isolated-restore procedure before targeting production.
- Reopen writes only after counts, invariants, queue state, storage, authentication, and critical browser/API journeys pass.
- Record incident timing, recovery point, operator/approver, checks, exceptions, and follow-up actions.

The complete target disaster-recovery workflow and pending production controls are tracked in `docs/planning/workflow.md` and Phase 6 of `docs/planning/task.md`.
