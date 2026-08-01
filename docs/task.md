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
- [ ] Document the backup location, restore command, retention, and responsible operator without committing credentials.

### Repository baseline

- [x] Record the complete legacy migration history in NBTS-NEW using the original migration filenames.
- [x] Confirm existing migrations remain marked as applied on the cloned database.
- [x] Confirm a fresh test database can build the entire schema from zero.
- [x] Compare table, column, index, and foreign-key definitions against the cloned database after additive security migrations.
- [ ] Establish CI checks for Pest, Pint, Larastan, and frontend builds.

### Documentation controls

- [x] Create `docs/task.md`.
- [x] Create `docs/achievement.md`.
- [x] Create `docs/workflow.md`.
- [ ] Add deployment, backup, recovery, API, and operator documentation as those features become real.
- [ ] Keep API and Flutter contract documentation synchronized with every API change.

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
- [ ] Add relationships for donor profile, center assignments, appointments, donations, eligibility, deferrals, loyalty, notifications, and FCM tokens.
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
- [ ] Ensure API responses expose stable codes rather than translated state values.

### Phase 1 completion gate

- [ ] Identity and authorization Pest suites pass.
- [ ] Existing users can authenticate in a cloned environment.
- [ ] Permission and center-scope tests prove isolation.
- [ ] English and Kiswahili smoke tests pass.
- [ ] No credentials or private Firebase keys are tracked by Git.

## Phase 2 — Core domain and operational services

### Models, data, and reusable code

- [x] Port and reconcile all deployed domain migrations without changing their historical contents.
- [-] Port models, relationships, casts, scopes, and factories; the donor, center, appointment, eligibility, deferral, donation, blood-unit, inventory, adjustment, and low-stock core is complete.
- [ ] Port policies and authorization tests.
- [ ] Port seeders for roles, permissions, centers, loyalty, content, and safe demo data.
- [ ] Port repositories only where they provide a tested query boundary.
- [ ] Remove stale, duplicate, generated, and environment-specific legacy code.

### Donors and centers

- [ ] Donor profile creation and donor ID generation.
- [ ] Donor search by QR payload, donor ID, phone, email, or name.
- [ ] Blood-center profile, location, opening hours, services, capacity, and status.
- [ ] Center staff assignment and active/inactive lifecycle.
- [ ] Preferred donor center.

### Eligibility and deferrals

- [ ] Screening records capture age, weight, health answers, notes, and staff member.
- [ ] Age, weight, donation interval, and active deferral rules are enforced.
- [ ] Temporary and permanent deferrals work.
- [ ] Deferral lifting records the actor and triggers reevaluation.
- [ ] Official male/female donation intervals are configurable and consistently applied.
- [ ] Staff remains the final authority for donor safety decisions.

### Appointments and walk-ins

- [ ] Donors can view slots and book appointments.
- [ ] Capacity and duplicate-booking rules are enforced.
- [ ] Donors can reschedule or cancel eligible appointments.
- [ ] Staff can confirm, reschedule, cancel, check in, and complete appointments.
- [ ] Walk-in donation flow works without an appointment.
- [ ] Appointment status transitions are explicit and audited.

### Donation recording

- [ ] Staff verifies donor identity and eligibility before completion.
- [ ] Appointment and walk-in donations are supported.
- [ ] Blood-group verification records verifier and time.
- [ ] Completing a donation updates donor history and next eligibility.
- [ ] Completion is transactional and idempotent.
- [ ] Failed or rejected attempts do not incorrectly increase inventory or loyalty.

### Blood units and inventory

- [ ] A completed donation creates one uniquely numbered blood unit.
- [ ] Unit statuses support collected, testing, available, reserved, transferred, used, rejected, expired, and discarded.
- [ ] Inventory is tracked by center and blood group.
- [ ] Status transitions adjust inventory exactly once.
- [ ] Manual adjustments require reason, notes, actor, and authorization.
- [ ] Inventory cannot go below zero.
- [ ] Expiry processing and disposal confirmation work.
- [ ] Adjustment history reconciles with inventory totals.

### Alerts, campaigns, loyalty, and notifications

- [ ] Low-stock thresholds open, update, notify, and resolve alerts.
- [ ] Emergency campaigns link to the triggering alert, center, and blood group.
- [ ] Eligible donor targeting respects location, eligibility, preferences, and consent.
- [ ] Badges, rewards, points, tiers, and leaderboards are deterministic.
- [ ] Notifications support in-app, push, SMS, and email channels.
- [ ] Appointment reminders avoid duplicates and respect preferences.
- [ ] Notification retries and failures are observable.

### Phase 2 completion gate

- [ ] End-to-end donation workflow tests pass.
- [ ] Inventory invariants and concurrency tests pass.
- [ ] Eligibility and deferral datasets pass.
- [ ] Notification channels are faked and asserted in automated tests.
- [ ] Fresh and existing database scenarios both pass.

## Phase 3 — Mobile API, Firebase, and Flutter

### API foundation

- [ ] Restore versioned `/api/v1` routing.
- [ ] Use Sanctum bearer tokens and API Resources.
- [ ] Preserve the stable response fields documented in `docs/workflow.md`.
- [ ] Add validation Form Requests and authorization policies.
- [ ] Add rate limiting, consistent errors, pagination, and API tests.

### Donor API capabilities

- [ ] Register, login, Firebase login, logout, and current user.
- [ ] Profile read/update and profile photo upload.
- [ ] Digital donor card and expiring QR payload.
- [ ] Eligibility, loyalty, leaderboard, and donation history.
- [ ] Centers, slots, campaigns, articles, publications, and schedules.
- [ ] Appointment list, upcoming, create, reschedule, and cancel.
- [ ] Notification list, unread count, read, delete, and device registration.

### Firebase and messaging

- [ ] Keep Firebase project `nbts-d567e` unless an explicit migration is approved.
- [ ] Generate a fresh backend service-account key and keep it outside Git.
- [ ] Configure Android package `com.nbts.mobile`.
- [ ] Configure iOS Firebase before claiming iOS support.
- [ ] Verify Firebase ID tokens against the configured project.
- [ ] Register, refresh, invalidate, and deduplicate FCM tokens.
- [ ] Send FCM HTTP v1 messages and handle invalid tokens safely.

### Flutter integration

- [ ] Establish the canonical Flutter workspace location.
- [ ] Update API base URL handling for local, staging, and production environments.
- [ ] Align every repository and model with the Laravel API contract.
- [ ] Verify Google and Apple authentication on supported platforms.
- [ ] Verify donor card, appointments, history, campaigns, centers, and notifications.
- [ ] Add or update Flutter tests for repositories, models, and critical screens.
- [ ] Update the mobile achievement evidence after device/emulator testing.

### Phase 3 completion gate

- [ ] Laravel API tests pass.
- [ ] Flutter static analysis and tests pass.
- [ ] Android end-to-end donor journey passes.
- [ ] iOS is either tested or clearly marked unsupported pending configuration.
- [ ] Push notification delivery is proven with a test device or Firebase test environment.

## Phase 4 — Public website

### Shared public experience

- [ ] Professional NBTS visual system, typography, spacing, colors, imagery, and motion.
- [ ] Responsive header, navigation, search access, language switcher, and footer.
- [ ] English and Kiswahili content strategy.
- [ ] Accessibility: semantic landmarks, skip link, focus states, alt text, contrast, and keyboard operation.
- [ ] SEO metadata, social metadata, favicon, canonical URLs, and helpful 404/500 pages.
- [ ] No invented statistics, contacts, downloads, or app-store links.

### Required public pages

- [ ] Home.
- [ ] About NBTS, mission, vision, governance, and leadership.
- [ ] Why donate and donation process.
- [ ] Services: collection, laboratory, blood products, clinical use, and quality management.
- [ ] Donor eligibility and deferral guidance.
- [ ] Apheresis donation guidance.
- [ ] Blood centers list, search, filters, and detail.
- [ ] Campaigns list, filters, and detail.
- [ ] News list and detail.
- [ ] Publications list, filters, detail/download, and document metadata.
- [ ] Public impact and analytics.
- [ ] Regional contacts and collection schedules.
- [ ] FAQ and donor safety guidance.
- [ ] Contact, feedback, complaints, and customer-service charter.
- [ ] Download app with verified links and QR target.
- [ ] Privacy policy, terms, and data-protection notice.

### Public content management

- [ ] Staff can manage articles, publications, campaigns, centers, schedules, regional contacts, FAQs, and approved metrics.
- [ ] Draft, review, publish, archive, and scheduled publishing states work.
- [ ] Media and document uploads are validated and auditable.
- [ ] Bilingual content has an explicit fallback policy.

### Phase 4 completion gate

- [ ] Public route tests pass.
- [ ] Content-management authorization tests pass.
- [ ] Desktop and mobile browser smoke tests have no JavaScript or console errors.
- [ ] Content is traceable to an approved source or managed backend record.

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
