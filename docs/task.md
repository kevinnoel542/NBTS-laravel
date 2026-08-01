# NBTS modernization task plan

Last updated: 2026-08-01

## Purpose

This is the live execution checklist for rebuilding NBTS on Laravel 13 without discarding the proven database, business rules, Firebase project, or Flutter application from the previous workspace.

Update this file whenever work changes state. Mark an item complete only when its implementation and the listed verification evidence both exist.

## Status legend

- `[ ]` Not started or not proven.
- `[-]` In progress.
- `[x]` Completed and verified.
- `[!]` Blocked by an external decision, credential, service, or environment.

## Delivery principles

- Preserve existing NBTS records and identifiers.
- Never run `migrate:fresh`, destructive seeders, or unreviewed schema changes against the shared database.
- Keep donors in the Flutter app and public information on the website.
- Use Fortify for staff/admin web authentication and security.
- Use Sanctum for mobile API tokens and Firebase for social identity and push notifications.
- Authorize staff operations with permissions and blood-center scope.
- Keep English and Kiswahili available throughout staff/admin accounts and public content.
- Put business rules in services/actions and enforce them consistently from web and API entry points.
- Record sensitive actions in an immutable audit trail.
- Add or update Pest tests for every functional change.
- Record only tested outcomes in `docs/achievement.md`.

## Verified starting point

- [x] Laravel 13, PHP 8.4, Fortify, Livewire 4, Flux UI 2, Tailwind CSS 4, and Pest 5 are installed.
- [x] The existing MySQL database schema is visible to NBTS-NEW.
- [x] Existing domain tables include donors, centers, appointments, donations, eligibility, blood units, inventory, campaigns, notifications, roles, and permissions.
- [x] Existing data includes users, donor profiles, donations, blood centers, and appointments.
- [x] The previous workspace workflows and project documentation have been reviewed and merged into `docs/workflow.md`.
- [x] The living task and achievement documents have been created.
- [x] A restorable database backup and isolated NBTS-NEW development database have been created.

## Phase 0 — Safety, documentation, and baseline

### Database safety

- [x] Create a timestamped backup of the current `nbts` database.
- [x] Verify the backup can be restored into an isolated database.
- [x] Point automated tests at a dedicated test database.
- [x] Point NBTS-NEW development at a clone rather than allowing both applications to write to the same working database.
- [x] Document the private backup location, isolated restore command, retention, and responsible operator roles without committing credentials in `docs/operations.md`.

### Repository baseline

- [x] Record the complete legacy migration history in NBTS-NEW using the original migration filenames.
- [x] Confirm existing migrations remain marked as applied on the cloned database.
- [x] Confirm a fresh test database can build the entire schema from zero.
- [x] Compare table, column, index, and foreign-key definitions against the cloned database after additive security migrations.
- [x] Establish MySQL-backed GitHub Actions checks for Pest, Pint, Larastan, clean npm installation, and the production frontend build.

### Documentation controls

- [x] Create `docs/task.md`.
- [x] Create `docs/achievement.md`.
- [x] Create `docs/workflow.md`.
- [x] Add current deployment, backup, isolated recovery, and operator procedures in `docs/operations.md`; production disaster-recovery expansion remains explicitly tracked in Phase 6.
- [x] Establish `docs/api.md` plus `docs/workflow.md` as the synchronized Laravel/Flutter v1 contract record, with a required change checklist and verification commands.

### Phase 0 completion gate

- [x] Backup restore succeeds.
- [x] Fresh schema migration succeeds.
- [x] Existing data counts remain unchanged after additive migrations.
- [x] Baseline automated tests and frontend build pass.

## Phase 1 — Dependencies, identity, access, and localization

### Package compatibility and installation

- [x] Confirm Laravel 13 compatible versions before changing dependencies.
- [x] Install and configure Laravel Sanctum for mobile API tokens.
- [x] Install and configure Spatie Laravel Permission for existing role/permission records.
- [x] Select and install maintained QR generation support.
- [x] Select and install maintained PDF generation support.
- [x] Select and install the backup implementation with a private local development storage disk.
- [x] Prefer native audit logging unless a package provides a clear, compatible operational advantage.
- [x] Run dependency security audits after installation.

### User model reconciliation

- [x] Merge the existing NBTS user fields into the new Fortify `User` model.
- [x] Preserve Fortify two-factor and passkey traits.
- [x] Preserve existing password hashes and remember tokens.
- [-] Add relationships for donor profile, center assignments, appointments, donations, eligibility, deferrals, loyalty, notifications, and FCM tokens; the donor, operations, notification, and FCM-token graph is complete, while loyalty remains pending.
- [x] Reconcile nullable mobile email with Fortify's email-first web flows.
- [ ] Remove role duplication as a source of authority; permissions must be canonical.
- [x] Keep legacy role data readable during migration.
- [x] Update the user factory with realistic donor/staff/admin states.

### Web authentication and security

- [ ] Existing staff/admin users can sign in without password resets.
- [x] Registration follows the approved account-creation policy.
- [x] Password reset and email verification work.
- [x] Two-factor setup, challenge, recovery codes, and disable flows work.
- [x] Passkey registration, login, confirmation, naming, and deletion work.
- [ ] Login, two-factor, passkey, and sensitive actions are rate limited.
- [x] Inactive users cannot authenticate or use active sessions.
- [x] Session security and password-confirmation boundaries are tested.

### Roles, permissions, and center scope

- [x] Restore `super_admin`, `nbts_admin`, `center_manager`, `center_staff`, and `donor` roles.
- [x] Define permissions for users, donors, centers, appointments, eligibility, donations, inventory, campaigns, content, notifications, reports, audits, backups, and settings.
- [ ] Super admins can manage the whole system.
- [ ] NBTS admins can manage national operations without unrestricted infrastructure access.
- [-] Center managers are restricted to assigned centers; the reusable center scope and blood-center policy are complete, while remaining record policies are pending.
- [-] Center staff can perform only assigned operational tasks at assigned centers; the permission matrix and center scope are complete, while remaining record policies are pending.
- [x] Donors cannot enter staff/admin routes.
- [ ] Policies cover every record-level action.

### English and Kiswahili

- [x] Configure `en` and `sw` application locales.
- [x] Add a persistent language switch endpoint and middleware foundation.
- [ ] Translate navigation, authentication, validation, empty states, system messages, PDF headings, and operational actions.
- [x] Store user language preference.
- [ ] Define which managed content fields are bilingual.
- [-] Ensure API responses expose stable codes rather than translated state values; the Firebase authentication/current-user resource does so, while remaining API resources are pending.

### Phase 1 completion gate

- [x] Identity and authorization Pest suites pass.
- [ ] Existing users can authenticate in a cloned environment.
- [x] Permission and center-scope tests prove isolation for the implemented policies and workflows.
- [ ] English and Kiswahili smoke tests pass.
- [x] No credentials or private Firebase keys are tracked by Git; service-account JSON paths are explicitly ignored.

## Phase 2 — Core domain and operational services

### Models, data, and reusable code

- [x] Port and reconcile all deployed domain migrations without changing their historical contents.
- [-] Port models, relationships, casts, scopes, and factories; the donor, center, appointment, eligibility, deferral, donation, blood-unit, inventory, adjustment, low-stock, campaign, article, notification, and FCM-token domains are complete, while loyalty and remaining staff-management domains are pending.
- [ ] Port policies and authorization tests.
- [ ] Port seeders for roles, permissions, centers, loyalty, content, and safe demo data.
- [ ] Port repositories only where they provide a tested query boundary.
- [ ] Remove stale, duplicate, generated, and environment-specific legacy code.

### Donors and centers

- [-] Donor profile creation and donor ID generation; the Firebase onboarding path is complete and other registration/reception entry points remain pending.
- [ ] Donor search by QR payload, donor ID, phone, email, or name.
- [-] Blood-center profile, location, opening hours, services, capacity, and status; active public/mobile read APIs with filters and compatibility fields are complete, while staff management UI remains pending.
- [ ] Center staff assignment and active/inactive lifecycle.
- [ ] Preferred donor center.

### Eligibility and deferrals

- [ ] Screening records capture age, weight, health answers, notes, and staff member.
- [-] Donation interval and active-deferral rules are enforced at completion; age and weight are captured by screening records but their screening action remains pending.
- [ ] Temporary and permanent deferrals work.
- [ ] Deferral lifting records the actor and triggers reevaluation.
- [x] Official NBTS Tanzania whole-blood intervals of three months for men and four months for women are configured and tested, with a conservative four-month fallback.
- [ ] Staff remains the final authority for donor safety decisions.

### Appointments and walk-ins

- [x] Donors can view slots and book appointments.
- [x] Capacity and duplicate-booking rules are enforced.
- [x] Donors can reschedule or cancel eligible appointments.
- [-] Staff can confirm, cancel, and complete appointments through the audited transition action; rescheduling and check-in remain pending.
- [x] Backend walk-in donation completion works without an appointment.
- [x] Appointment status transitions are explicit, center-authorized, transactional, and audited.

### Donation recording

- [-] Staff eligibility and center authority are enforced before completion; donor lookup and UI identity verification remain pending.
- [x] Appointment and walk-in donation completion are supported by the shared transactional action.
- [x] Blood-group verification records verifier and time.
- [x] Completing a donation updates donor history and next eligibility.
- [-] Completion is transactional and protected by uniqueness constraints; request-level API idempotency keys remain pending.
- [x] Failed or rejected attempts do not incorrectly increase inventory or loyalty.

### Blood units and inventory

- [x] A completed donation creates one uniquely numbered blood unit.
- [x] Unit status codes support collected, testing, available, reserved, transferred, used, rejected, expired, and discarded; the transition action remains pending.
- [x] Inventory is tracked by center and blood group.
- [x] Audited blood-unit status transitions adjust available and reserved inventory exactly once.
- [ ] Manual adjustments require reason, notes, actor, and authorization.
- [x] Available and reserved inventory cannot go below zero.
- [ ] Expiry processing and disposal confirmation work.
- [-] Automated transition adjustment history reconciles with inventory totals; manual-adjustment and full historical reconciliation commands remain pending.

### Alerts, campaigns, loyalty, and notifications

- [-] Low-stock thresholds open, update, and resolve alerts transactionally; notification dispatch remains pending.
- [ ] Emergency campaigns link to the triggering alert, center, and blood group.
- [ ] Eligible donor targeting respects location, eligibility, preferences, and consent.
- [ ] Badges, rewards, points, tiers, and leaderboards are deterministic.
- [ ] Notifications support in-app, push, SMS, and email channels.
- [ ] Appointment reminders avoid duplicates and respect preferences.
- [ ] Notification retries and failures are observable.

### Phase 2 completion gate

- [-] The backend completion-to-unit-to-inventory workflow tests pass; screening input, UI, and notification layers remain pending.
- [-] Inventory locking, exactly-once deltas, reconciliation, and negative-balance tests pass; parallel-process contention tests remain pending.
- [ ] Eligibility and deferral datasets pass.
- [ ] Notification channels are faked and asserted in automated tests.
- [ ] Fresh and existing database scenarios both pass.

## Phase 3 — Mobile API, Firebase, and Flutter

### API foundation

- [x] Restore versioned `/api/v1` routing.
- [x] Use Sanctum bearer tokens and API Resources.
- [-] Preserve the stable response fields documented in `docs/workflow.md`; all currently implemented donor/mobile capability groups are stable, while loyalty and leaderboard compatibility remain pending.
- [-] Add validation Form Requests and authorization policies; Firebase authentication validation and account boundaries are complete, while remaining endpoint policies are pending.
- [-] Add rate limiting, consistent errors, pagination, and API tests; authentication throttling/localized errors and bounded center, appointment, and donation pagination are complete, while remaining API groups are pending.

### Donor API capabilities

- [x] Register, login, Firebase login, logout, and current user.
- [x] Profile read/update and profile photo upload.
- [x] Digital donor card and expiring signed QR payload.
- [-] Eligibility, loyalty, leaderboard, and donation history; read-only personal eligibility plus donation history/summary are complete, while loyalty and leaderboard APIs remain pending.
- [x] Centers, slots, campaigns, articles, publications, and schedules discovery APIs.
- [x] Appointment list, upcoming, create, reschedule, and cancel.
- [x] Notification list, unread count, read, delete, and device registration.

### Firebase and messaging

- [x] Keep Firebase project `nbts-d567e` unless an explicit migration is approved.
- [!] Generate a fresh backend service-account key and keep it outside Git; code/config boundaries are ready, but key rotation requires Firebase Console access.
- [-] Configure Android package `com.nbts.mobile`; the canonical old client is confirmed on this package/project, while migration and device verification remain pending.
- [ ] Configure iOS Firebase before claiming iOS support.
- [x] Verify Firebase ID tokens with the Laravel Firebase Admin bridge, including revocation checks; live credentials still require the fresh external key above.
- [x] Register, refresh/reassign, invalidate, and deduplicate FCM tokens.
- [ ] Send FCM HTTP v1 messages and handle invalid tokens safely.

### Flutter integration

- [x] Establish the canonical Flutter workspace location as the standalone `NBTS/nbts-mobile` Git repository; `NBTS/database/nbts-mobile` is an older divergent duplicate and will not be migrated.
- [x] Update API base URL handling for local, staging, and production environments.
- [-] Align every repository and model with the Laravel API contract; existing auth/profile/center/appointment/card/eligibility/history/campaign/article/notification repositories and models match v1, while loyalty and new publication/schedule client surfaces remain pending.
- [ ] Verify Google and Apple authentication on supported platforms.
- [-] Verify donor card, appointments, history, campaigns, centers, and notifications; parser contract tests are added but cannot run until Flutter tooling is installed, and emulator/device verification remains pending.
- [-] Add or update Flutter tests for repositories, models, and critical screens; API URL/header/payload and core model contract tests are added, while repository and screen coverage remains pending.
- [ ] Update the mobile achievement evidence after device/emulator testing.

### Phase 3 completion gate

- [-] Authentication, profile, center, appointment, donor-card, eligibility, donation-history, public-content, notification, and FCM-token API tests pass; loyalty and Flutter integration gates remain pending.
- [!] Flutter static analysis and tests cannot run on the current workstation because neither `flutter` nor `dart` is installed.
- [ ] Android end-to-end donor journey passes.
- [ ] iOS is either tested or clearly marked unsupported pending configuration.
- [ ] Push notification delivery is proven with a test device or Firebase test environment.

## Phase 4 — Public website

### Shared public experience

- [x] Professional NBTS visual system, typography, spacing, colors, imagery, and restrained motion.
- [x] Responsive header, navigation, directory search, language switcher, and footer.
- [x] English and Kiswahili public shell and static content, with database-managed records safely falling back to their stored language.
- [x] Accessibility: semantic landmarks, skip link, focus states, alt text, contrast, keyboard operation, and reduced-motion handling.
- [-] SEO metadata, social metadata, favicon, canonical URLs, and helpful 404/500 pages; descriptive page titles are complete, while the remaining metadata and error-page work is pending.
- [x] No invented statistics, contacts, downloads, or app-store links.

### Required public pages

- [x] Home.
- [x] About NBTS, mission, vision, governance, and leadership.
- [x] Why donate and donation process.
- [x] Services: collection, laboratory, blood products, clinical use, and quality management.
- [x] Donor eligibility and deferral guidance.
- [x] Apheresis donation guidance.
- [x] Blood centers list, search, filters, and detail.
- [x] Campaigns list, filters, and detail.
- [x] News list and detail.
- [-] Publications list, download, and document metadata are complete; a dedicated publication detail route and staff filters remain pending.
- [x] Public impact and analytics using aggregate, non-sensitive source records.
- [-] Regional contacts and center opening schedules are available through center/contact pages; dedicated collection-schedule management remains pending.
- [x] FAQ and donor safety guidance.
- [x] Contact, feedback, complaints, and customer-service guidance.
- [-] Download-app guidance and an anonymized app preview are complete; store links and a QR target remain intentionally absent until approved destinations are provided.
- [ ] Privacy policy, terms, and data-protection notice.

### Public content management

- [ ] Staff can manage articles, publications, campaigns, centers, schedules, regional contacts, FAQs, and approved metrics.
- [ ] Draft, review, publish, archive, and scheduled publishing states work.
- [ ] Media and document uploads are validated and auditable.
- [ ] Bilingual content has an explicit fallback policy.

### Phase 4 completion gate

- [x] Public route, visibility, filtering, localization, and aggregate-data tests pass.
- [ ] Content-management authorization tests pass.
- [x] Desktop 1600×900 and representative mobile browser smoke tests pass without JavaScript errors or horizontal overflow.
- [x] Dynamic content is sourced from managed backend records; static copy and approved visual assets were reconciled from the previous NBTS workspace without copying credentials.

## Phase 5 — Workflow-driven staff and administrator accounts

### Navigation architecture

- [ ] Overview.
- [ ] Donor reception: search, scan, registration, and donor profile.
- [ ] Appointments: today, upcoming, pending, and check-in.
- [ ] Eligibility: screening queue, deferrals, and history.
- [ ] Donations: record donation, verify blood group, and history.
- [ ] Blood operations: testing queue, blood units, inventory, transfers, expiry, and disposal.
- [ ] Response: low-stock alerts, campaigns, and donor communication.
- [ ] Engagement: notifications, loyalty, rewards, and leaderboard.
- [ ] Content: news, publications, FAQs, schedules, and public pages.
- [ ] Intelligence: reports, analytics, and exports.
- [ ] Administration: users, roles, permissions, centers, settings, audit, and recovery.
- [ ] Account: profile, language, appearance, password, 2FA, passkeys, and sessions.

### Account experience requirements

- [ ] Navigation reflects the real operational sequence and user permissions.
- [ ] Center-scoped users always see their active center context.
- [ ] Every page has useful search, filters, sorting, pagination, bulk actions, exports, empty states, loading states, and recoverable errors where appropriate.
- [ ] High-risk actions require confirmation and reason capture.
- [ ] Dashboard metrics link to actionable work queues.
- [ ] Mobile/tablet layouts support reception and floor operations.
- [ ] Icons use Lucide where requested and remain consistent in stroke and meaning.
- [ ] Dark mode follows the established application convention.

### Phase 5 completion gate

- [ ] Livewire component and authorization tests pass.
- [ ] Each role completes its allowed workflow and is blocked from forbidden workflows.
- [ ] English and Kiswahili browser journeys pass.
- [ ] Desktop 1600×900 and representative tablet/mobile layouts pass visual QA.

## Phase 6 — Audit, disaster recovery, PDFs, and reporting

### Audit trail

- [ ] Record actor, action, subject, center, request identifier, before/after values, IP, user agent, and timestamp.
- [ ] Audit authentication/security changes, user and permission changes, eligibility, deferrals, donation completion, blood-group verification, inventory, campaigns, notifications, content publishing, exports, backups, and restores.
- [ ] Protect audit records from ordinary editing and deletion.
- [ ] Provide permission-controlled filters and export.
- [ ] Define retention and sensitive-value redaction rules.

### Disaster recovery

- [ ] Automated encrypted database backups.
- [ ] Media/document backup coverage.
- [ ] Off-site storage and retention policy.
- [ ] Scheduled backup verification.
- [ ] Documented restore runbook and recovery roles.
- [ ] Restore drill in an isolated environment.
- [ ] Recovery point and recovery time objectives.
- [ ] Audit all manual backup and restore operations.

### PDF and exports

- [ ] Donor card/summary PDF.
- [ ] Appointment confirmation PDF.
- [ ] Eligibility assessment PDF.
- [ ] Donation receipt/certificate PDF.
- [ ] Blood unit traceability PDF.
- [ ] Inventory, expiry, and adjustment reports.
- [ ] Campaign, donation, center, and national summary reports.
- [ ] Audit report and disaster-recovery status report.
- [ ] English/Kiswahili labels, branding, access control, generation tests, and stable filenames.

### Phase 6 completion gate

- [ ] Audit completeness tests pass for every sensitive workflow.
- [ ] Backup and isolated restore are proven.
- [ ] PDF generation and authorization tests pass.
- [ ] Reports reconcile with source records.

## Phase 7 — Full-system verification and release

### Automated verification

- [ ] Run the complete Pest suite.
- [ ] Run Flutter tests and static analysis.
- [ ] Run Pint on changed PHP files.
- [ ] Run Larastan at the agreed level.
- [ ] Run frontend production build.
- [ ] Run Composer and npm security audits.
- [ ] Verify queue workers, scheduler, mail, SMS, Firebase, storage, and cache configuration.

### Browser and device QA

- [ ] Load the agent-browser core workflow before browser commands.
- [ ] Open the system in an agent-browser session at 1600×900.
- [ ] Test every public route.
- [ ] Test staff/admin navigation and primary workflows for each role.
- [ ] Test English and Kiswahili.
- [ ] Test keyboard focus, form validation, loading, empty, error, and confirmation states.
- [ ] Check browser console and Laravel logs.
- [ ] Capture final screenshots for key public and account pages.
- [ ] Test representative mobile and tablet viewports.

### Release and cutover

- [ ] Freeze legacy writes for the final migration window.
- [ ] Take a final verified backup.
- [ ] Apply only reviewed additive migrations.
- [ ] Compare record counts and operational totals before and after cutover.
- [ ] Warm caches, start queues/scheduler, and run smoke tests.
- [ ] Monitor authentication, API errors, jobs, notifications, and inventory workflows.
- [ ] Retain a tested rollback procedure until acceptance is complete.

### Final completion gate

- [ ] Every item in this task plan is complete or explicitly accepted as out of scope by the user.
- [ ] `docs/achievement.md` contains evidence for every delivered feature.
- [ ] Web and mobile critical journeys pass.
- [ ] Recovery and rollback are proven.
- [ ] No unresolved high-severity security, data-integrity, or accessibility issue remains.
