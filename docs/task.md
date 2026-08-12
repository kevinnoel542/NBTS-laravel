# NBTS modernization task plan

Last updated: 2026-08-12

## Purpose

This is the live execution checklist for rebuilding NBTS on Laravel 13 without discarding the proven database, business rules, Firebase project, or Flutter application from the previous workspace.

Update this file whenever work changes state. Mark an item complete only when its implementation and the listed verification evidence both exist.


## Roadmap boundary

This roadmap separates two truths:

- The completed entries prove a strong donor-engagement, public-information, mobile-API, identity, center-scope, appointment, donation, audit, and basic inventory foundation.
- The target national blood-management service is not complete until laboratory, quarantine/release, component lineage, hospital clinical use, cold chain, haemovigilance, recall/look-back, quality management, resilience, and controlled rollout are implemented and validated.

A roadmap item must not be marked complete because a screen, model, API, copied legacy feature, or unit test exists in isolation. Safety-critical completion requires the stated domain, authorization, traceability, operational, approval, and recovery evidence.

## Priority and safety legend

Priority:

- `Must`: required before the affected safety-critical workflow can enter production.
- `Should`: high-value capability for controlled scale-up after the core safety chain is stable.
- `Could`: later optimization after safety, data quality, and adoption are proven.

Safety classification:

- `Critical`: failure can release unsafe blood, misidentify a donor/sample/component/patient, lose traceability, or delay emergency care.
- `High`: failure can cause serious shortage, wastage, privacy harm, prolonged outage, or incorrect clinical/operational decisions.
- `Standard`: important service, usability, engagement, or administrative work without direct release/transfusion authority.

## Requirement and evidence convention

- Requirement IDs use stable prefixes such as `GOV`, `CTR`, `DON`, `SCR`, `COL`, `ID`, `LAB`, `REL`, `CMP`, `INV`, `CC`, `LOG`, `HSP`, `XMT`, `TRF`, `HV`, `QMS`, `SEC`, `INT`, `DR`, `UX`, `RPT`, and `ROLLOUT`.
- Every implementation task must state owner, priority, safety class, dependencies, acceptance tests, and approval evidence.
- `docs/achievement.md` records only verified implementation; roadmap decisions and unexecuted tests remain here.

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
- [x] Add current deployment, backup, isolated recovery, and operator procedures in `docs/operations.md`; production disaster-recovery expansion remains explicitly tracked in Phase 11.
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
- [x] Add relationships for donor profile, center assignments, appointments, donations, eligibility, deferrals, loyalty, notifications, and FCM tokens.
- [x] Reconcile nullable mobile email with Fortify's email-first web flows.
- [x] Remove role duplication as a source of authority; permissions must be canonical, with `users.role` retained only as migration/backfill compatibility data.
- [x] Keep legacy role data readable during migration.
- [x] Update the user factory with realistic donor/staff/admin states.

### Web authentication and security

- [x] Existing staff/admin users can sign in without password resets.
- [x] Registration follows the approved account-creation policy.
- [x] Password reset works; email verification and resend controls are intentionally disabled during system construction by product decision, and unverified staff access is covered by regression tests. Re-enablement remains a production-readiness decision.
- [x] Two-factor setup, challenge, recovery codes, and disable flows work.
- [x] Passkey registration, login, confirmation, naming, and deletion work.
- [x] Login, two-factor, passkey, password recovery/confirmation, security settings, profile, verification resend, and account-deletion actions are rate limited.
- [x] Inactive users cannot authenticate or use active sessions.
- [x] Session security and password-confirmation boundaries are tested.

### Roles, permissions, and center scope

- [x] Restore `super_admin`, `nbts_admin`, `center_manager`, `center_staff`, and `donor` roles.
- [x] Define permissions for users, donors, centers, appointments, eligibility, donations, inventory, campaigns, content, notifications, reports, audits, backups, and settings.
- [x] Super admins can manage the whole system through the active-account global authorization override.
- [x] NBTS admins can manage national operations without unrestricted infrastructure access.
- [x] Center managers are restricted to assigned centers across donor and operational record policies.
- [x] Center staff can perform only assigned operational tasks at assigned centers.
- [x] Donors cannot enter staff/admin routes.
- [x] Policies cover every application record model and its supported record-level actions.

### English and Kiswahili

- [x] Configure `en` and `sw` application locales.
- [x] Add a persistent language switch endpoint and middleware foundation.
- [x] Translate navigation, authentication, validation, empty states, system messages, PDF headings, and operational actions.
- [x] Store user language preference.
- [x] Define which managed content fields are bilingual.
- [x] Ensure API responses expose stable codes rather than translated state values.

### Phase 1 completion gate

- [x] Identity and authorization Pest suites pass.
- [x] Existing users can authenticate in a cloned environment.
- [x] Permission and center-scope tests prove isolation for the implemented policies and workflows.
- [x] English and Kiswahili smoke tests pass.
- [x] No credentials or private Firebase keys are tracked by Git; service-account JSON paths are explicitly ignored.

## Phase 2 — Core domain and operational services

### Models, data, and reusable code

- [x] Port and reconcile all deployed domain migrations without changing their historical contents.
- [x] Port models, relationships, casts, scopes, and factories for all deployed domain records.
- [x] Port policies and authorization tests.
- [x] Port idempotent seeders for roles, permissions, centers, loyalty, content, and safe local/testing demo data.
- [x] Port repositories only where they provide a tested query boundary; the legacy repositories were intentionally replaced by tested actions, services, and model scopes.
- [x] Remove stale, duplicate, generated, and environment-specific legacy code from the porting scope.

### Donors and centers

- [x] Donor profile creation and donor ID generation work through Firebase onboarding and staff reception registration.
- [x] Donor search supports QR payload, donor ID, phone, email, and name with center-aware results.
- [x] Blood-center profile, location, opening hours, services, capacity, and status are managed through authorized actions and exposed through staff/public/mobile views.
- [x] Center staff assignment and active/inactive lifecycle are authorized, audited, and tested.
- [x] Preferred donor center is stored, updated, scoped, and exposed through the mobile profile contract.

### Eligibility and deferrals

- [x] Screening records capture age, weight, health answers, notes, and staff member.
- [x] Donation interval, age, weight, consent, and active-deferral rules are enforced by the screening and completion actions.
- [x] Temporary and permanent deferrals work.
- [x] Deferral lifting records the actor and triggers a safe reevaluation that requires fresh staff screening.
- [x] Official NBTS Tanzania whole-blood intervals of three months for men and four months for women are configured and tested, with a conservative four-month fallback.
- [x] Staff remains the final authority for donor safety decisions.

### Appointments and walk-ins

- [x] Donors can view slots and book appointments.
- [x] Capacity and duplicate-booking rules are enforced.
- [x] Donors can reschedule or cancel eligible appointments.
- [x] Staff can confirm, reschedule, check in, cancel, mark no-show, and complete appointments through authorized audited actions.
- [x] Backend walk-in donation completion works without an appointment.
- [x] Appointment status transitions are explicit, center-authorized, transactional, and audited.

### Donation recording

- [x] Staff eligibility, center authority, donor lookup, and explicit UI blood-group verification are enforced before completion.
- [x] Appointment and walk-in donation completion are supported by the shared transactional action.
- [x] Blood-group verification records verifier and time.
- [x] Completing a donation updates donor history and next eligibility.
- [x] Completion is transactional, protected by uniqueness constraints, and safely replayable through request-level idempotency keys.
- [x] Failed or rejected attempts do not incorrectly increase inventory or loyalty.

### Blood units and inventory

- [x] The current compatibility workflow creates one uniquely numbered legacy blood-unit record per completed donation. The target collection-container and zero-or-more component lineage model remains pending in Phases 6–8.
- [x] The existing compatibility status codes and audited transition action support collected, testing, available, reserved, transferred, used, rejected, expired, and discarded. The expanded specimen, quarantine, release, component, issue, dispatch, receipt, transfusion, return, and recall state models remain pending.
- [x] Inventory is tracked by center and blood group.
- [x] Audited blood-unit status transitions adjust available and reserved inventory exactly once.
- [x] Manual adjustments require reason, notes, actor, and authorization.
- [x] Available and reserved inventory cannot go below zero.
- [x] Expiry processing and disposal confirmation work.
- [x] Automated and manual adjustment history reconciles with inventory totals through the authorized UI and reconciliation command.

### Alerts, campaigns, loyalty, and notifications

- [x] Low-stock thresholds open, update, and resolve alerts transactionally and connect to donor-notification actions.
- [x] Emergency campaigns link to the triggering alert, center, and blood group.
- [x] Eligible donor targeting respects location, eligibility, blood group, preferences, and consent.
- [x] Badges, rewards, points, tiers, and leaderboards are deterministic.
- [x] Notifications support in-app, push, SMS, and email channels through queued channel adapters.
- [x] Appointment reminders avoid duplicates and respect per-channel preferences.
- [x] Notification retries and failures are recorded and filterable in the Engagement delivery-status workspace.

### Phase 2 completion gate

- [x] The backend completion-to-unit-to-inventory workflow, screening UI, and notification-layer tests pass.
- [x] Inventory locking, exactly-once deltas, reconciliation, negative-balance, and parallel-process contention tests pass.
- [x] Eligibility and deferral datasets pass.
- [x] Notification channels are faked and asserted in automated tests.
- [x] Fresh and existing database scenarios both pass.
- [x] Staff navigation follows the operational workflow order, has a persistent animated desktop collapse state, keeps every permitted destination available as a labelled icon, and passes visible 1600×900 browser QA.

## Phase 3 — Mobile API, Firebase, and Flutter

### API foundation

- [x] Restore versioned `/api/v1` routing.
- [x] Use Sanctum bearer tokens and API Resources.
- [x] Preserve the stable response fields documented in `docs/workflow.md`, including loyalty, leaderboard, publications, and donation-schedule compatibility aliases.
- [x] Keep `docs/api.md` as the detailed mobile handoff with all v1 routes, authentication, request bodies, filters, envelopes, errors, response fields, aliases, Firebase backend configuration, and client implementation guidance.
- [x] Add validation Form Requests and authorization/ownership boundaries to the implemented donor API groups.
- [x] Add authentication rate limiting, consistent localized errors, bounded pagination, and API tests.

### Donor API capabilities

- [x] Register, login, Firebase login, logout, and current user.
- [x] Profile read/update and profile photo upload.
- [x] Digital donor card and expiring signed QR payload.
- [x] Eligibility, loyalty, privacy-safe leaderboard, and donation history/summary.
- [x] Centers, slots, campaigns, articles, publications, and schedules discovery APIs.
- [x] Appointment list, upcoming, create, reschedule, and cancel.
- [x] Notification list, unread count, read, delete, and device registration.

### Firebase and messaging

- [x] Keep Firebase project `nbts-d567e` unless an explicit migration is approved.
- [!] Generate a fresh backend service-account key and keep it outside Git; code/config boundaries are ready, but key rotation requires Firebase Console access.
- [x] Configure Android package `com.nbts.mobile` against Firebase project `nbts-d567e`; physical-device verification remains a completion-gate item.
- [!] Configure iOS Firebase before claiming iOS support; iOS is explicitly unsupported until its bundle ID and `GoogleService-Info.plist` are approved.
- [x] Verify Firebase ID tokens with the Laravel Firebase Admin bridge, including revocation checks; live credentials still require the fresh external key above.
- [x] Register, refresh/reassign, invalidate, and deduplicate FCM tokens.
- [x] Send FCM HTTP v1 messages through the configured Firebase transport, retire invalid/unknown tokens safely, and audit only token fingerprints.

### Flutter integration

- [!] Further Flutter implementation and device evidence are owned by the separate mobile developer. Laravel changes must keep the tested contract and `docs/api.md` synchronized without editing the mobile repository unless explicitly requested.
- [x] Establish the canonical Flutter workspace location as the standalone `NBTS/nbts-mobile` Git repository; `NBTS/database/nbts-mobile` is an older divergent duplicate and will not be migrated.
- [x] Update API base URL handling for local, staging, and production environments.
- [x] Align repositories and models with the implemented Laravel v1 contract, including loyalty, leaderboard, publications, and donation schedules.
- [ ] Verify Google and Apple authentication on supported platforms.
- [-] Verify donor card, appointments, history, campaigns, centers, and notifications; parser/repository contract tests pass, while emulator/device verification remains pending.
- [-] Add or update Flutter tests for repositories, models, and critical screens; the 8-test API/model/repository plus welcome/registration suite passes, while broader critical-screen device coverage remains pending.
- [ ] Update the mobile achievement evidence after device/emulator testing.

### Phase 3 completion gate

- [x] Authentication, profile, center, appointment, donor-card, eligibility, donation-history, loyalty, leaderboard, public-content, notification, FCM-token, and FCM transport API tests pass.
- [x] Flutter 3.44/Dart 3.12 static analysis passes with no issues and all 8 Flutter tests pass in an isolated Docker SDK.
- [ ] Android end-to-end donor journey passes.
- [x] iOS is clearly marked unsupported pending an approved bundle ID and Firebase configuration.
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

## Phase 5 — Operating model, center hierarchy, roles, and overview

### Controlled operating-model documents

- [~] `system-overview.md` defines the verified foundation, target boundaries, module map, role-aware dashboard contract, and implementation constraints; external domain approvals remain pending.
- [~] `center-operating-model.md` defines the additive hierarchy, center capability, department/location, assignment lifecycle, active-context, migration, and separation-of-duty contract; external operations/clinical approval remains pending.
- [~] `roles-and-permissions.md` defines the 26 target profiles, transition compatibility roles, permission matrices, dashboard mappings, and duty-separation contract; external security/clinical/quality/hospital approval remains pending.

### GOV-STRUCT — National and center hierarchy

Priority: Must. Safety: High.

- [~] Product implementation uses the approved additive hierarchy—NBTS national level → optional zone/region → blood center or hospital interface → department → storage/work location; final Ministry/NBTS structure approval remains pending.
- [x] Define and implement the authoritative organization registry foundation with stable codes, parent relationships, unit types, lifecycle states, and blood-center links.
- [ ] Approve the center-type catalog after current-state discovery. Candidate types for assessment are full collection/testing/processing center, collection-only site or mobile team, testing/processing hub, storage/distribution hub, and hospital blood-bank interface.
- [ ] Define which functions each center type may perform; the system must not expose a laboratory, release, component, storage, or issue action to a center that is not approved for it.
- [ ] Define center opening hours, service capacity, supported donation methods, tests, components, storage devices, transport routes, hospitals served, emergency contacts, and downtime capability.
- [~] Active, suspended, temporarily closed, and retired organization states are implemented; the formal state-change approval/effective-date procedure remains pending.

### GOV-DEPT — Departments and module ownership

Priority: Must. Safety: High.

- [x] Establish department and work-location records plus scoped operational assignments for the documented operational areas.
- [ ] Assign one accountable owner and escalation route to every module and queue.
- [x] Support multiple effective-dated assignments for one identity across permitted organization, department, and location scopes.
- [~] Assignment permissions cannot bypass active scope or self-approve a clinical assignment; later record-level clinical separation rules remain pending with their workflows.

### GOV-ROLE — Operational roles and assignments

Priority: Must. Safety: Critical for release/transfusion roles.

Existing broad roles remain the compatibility boundary: `super_admin`, `nbts_admin`, `center_manager`, `center_staff`, and `donor`.

Add the approved 26-profile operating catalogue and migrate from the broad compatibility roles without breaking current accounts:

- [x] Platform: super administrator and ICT/security operator.
- [x] National: national operations administrator, national quality/haemovigilance officer, national inventory/logistics coordinator, national donor engagement/content officer, data-protection/governance officer, and national auditor/inspector.
- [x] Center: center manager, reception officer, screening/counselling officer, collection/phlebotomy officer, laboratory technician, laboratory approver/quality officer, component-processing officer, inventory officer, logistics/cold-chain officer, haemovigilance/quality officer, and center read-only auditor.
- [x] Hospital/integration: hospital clinician/requester, hospital blood-bank officer, compatibility/crossmatch officer, transfusion nurse/officer, hospital haemovigilance officer, and hospital read-only reviewer.
- [x] Donor: donor mobile role with access limited to the person’s own records and approved public data.
- [x] Enforce one identity account per person while supporting multiple scoped role, organization/center/hospital, department, location, shift, and effective-date assignments.
- [x] Record organization, center link, department, location, role/profile, shift, effective start/end date, assignment status, approver, revoker, and reason.
- [x] Support staff assigned to one or more centers while enforcing an ownership-checked active assignment and selected-center context.
- [~] Assignment, account, organization, department, and location state changes remove affected access; competency records exist, while action-specific competency enforcement remains pending with the later clinical workflows.
- [x] Maintain five permission-tested representative construction accounts with local-only credentials: super administrator, NBTS administrator, center manager, center staff, and donor.

### GOV-SOD — Separation of duties and approvals

Priority: Must. Safety: Critical.

- [ ] A collector cannot silently relabel or replace a specimen after collection.
- [ ] A laboratory technician cannot be the sole approver and releaser of the same component.
- [ ] A release approver cannot bypass incomplete, reactive, discrepant, failed-QC, expired, recalled, or unresolved cold-chain conditions.
- [ ] Blood-group correction after verification requires elevated permission, reason, confirmation, and independent review.
- [ ] Emergency release, manual inventory adjustment, disposal, recall closure, and high-risk configuration changes require explicit authority and audit.
- [x] Super-admin technical access does not automatically grant clinical release authority.
- [ ] Define configurable dual authorization by action, center type, component, emergency state, and risk class.

### GOV-VIS — Data visibility

Priority: Must. Safety: High.

- [x] Donors see only their own records and approved public data.
- [x] Center staff see only assigned centers, permitted departments, and minimum necessary implemented donor information.
- [x] Center managers see assigned-center operations without automatically receiving infrastructure or clinical authority.
- [x] National operational users see implemented national data according to explicit permission.
- [ ] Auditors use read-only access with export controls and purpose logging.
- [~] Hospital assignment isolation is implemented and tested; request, allocation, issue, and patient-linked workflows remain unavailable pending later approval and implementation.
- [ ] Analytics use de-identified data unless identified access is specifically authorized.

### GOV-OVERVIEW — Dashboards and actionable queues

Priority: Must. Safety: High.

- [x] Build one shared role-aware dashboard shell instead of duplicating complete pages per profile.
- [x] Implement the 13 staff configurations defined in `docs/workflow.md`: system control, national operations, national quality/governance, national inventory/logistics, engagement/content, center management, reception, screening/counselling, collection, laboratory/components, center inventory/logistics, center quality/haemovigilance, and hospital operations.
- [x] Keep the donor home as a separate mobile/API dashboard contract without modifying the Flutter code in this Laravel workstream.
- [x] Show active role and national/center/hospital context in the heading, with a concise responsibility summary and an assignment switcher only where multiple assignments exist.
- [x] Recalculate navigation, dashboard data, queues, actions, and scope whenever the active assignment changes.
- [~] Implemented priority queues place current actionable work first and hide unauthorized/unsupported sections; later safety-domain SLA and escalation queues remain pending with those modules.
- [x] Use compact connected metric strips and content-sized queue panels without oversized cards or empty card gaps.
- [ ] Every metric links to the records or queue that explains it; no decorative totals without action or definition.
- [ ] Critical queues have age, SLA, owner, escalation state, and overdue indicators.
- [x] Quick actions are role-appropriate and permission checked; later clinical actions remain hidden rather than bypassing competency, reason, audit, or independent approval.
- [~] Shared dashboard components support 1600×900 responsive layouts, English/Kiswahili, keyboard focus, reduced motion, and implemented empty states; later deferred/error regions remain pending where their modules do not yet exist.

### Phase 5 completion gate

- [~] Product-owner implementation approval is recorded; Ministry/NBTS operations, clinical, laboratory, quality, hospital, privacy, center-capability, and final separation-of-duty approval remains pending.
- [x] Permission and assignment tests prove implemented center, department/context, national, hospital, audit, donor, and technical isolation.
- [x] All 26 target profiles map to the correct overview among 13 shared configurations, and direct forbidden actions remain permission denied or unavailable.
- [x] Assignment suspension, expiry, and revocation immediately remove current access without deleting historical accountability.
- [x] Five compatibility accounts and visible 1600×900 dark/light browser QA pass; all four discovered dashboard issues are resolved with evidence.

## Phase 6 — Donor reception, screening, collection, and identification

### DON-MASTER — Donor identity and duplicate resolution

Priority: Must. Safety: Critical.

- [x] Stable donor IDs, profiles, compact reception worklists, registration, center scope, and controlled identity actions are implemented.
- [x] Signed donor-card QR, donor ID, phone, email, and name search use scoped previews and require a separate expiring identity confirmation before clinical work.
- [!] Approved national-identifier lookup remains disabled until the authoritative source, legal basis, matching rules, retention, and operator access are externally approved.
- [x] Possible duplicates are scored from normalized identity signals; registration blocks likely matches unless an authorized reason is recorded.
- [x] Review supports not-a-match or merge decisions; merge moves operational history, disables the source account, and preserves an immutable alias and audit provenance instead of deleting either identity.
- [x] Pending duplicate review, merged/inactive accounts, effective deferrals, and donation intervals are rechecked when screening and collection begin.
- [x] Reception captures the construction privacy-notice version, consent time/source, channel preferences, preferred language, and preferred center.

### SCR-ELIG — Screening and eligibility

Priority: Must. Safety: Critical.

- [x] Screening stores donor/appointment/identity links, questionnaire and rule versions, answers, age, weight, haemoglobin, observations, actor, center, time, decision, source mode, counselling, referral, and re-entry evidence.
- [x] A versioned rule engine enforces age, weight, interval, health answers, temporary/permanent deferrals, and confidential self-exclusion; only construction values are active today.
- [!] Clinical/NBTS owners must approve the production questionnaire, thresholds, decision codes, referral rules, and effective date before the construction protocol can be promoted.
- [x] The authorized screening officer remains the decision maker; an unsafe eligible override requires elevated authority and a documented reason.
- [x] Stable decision codes and exact protocol/rule/questionnaire snapshots are retained with every screening record.
- [x] Private counselling, referral, re-entry date, and a generic follow-up notification are supported; notification text never copies the deferral or self-exclusion reason into SMS, email, or push content.
- [x] Lifting or changing a deferral remains authority-, reason-, reevaluation-, and audit-controlled.

### COL-ID — Unique donation identifier and barcode chain

Priority: Must. Safety: Critical.

- [!] ISBT 128 or the approved national equivalent, production label layouts, product codes, scanner/printer validation, and migration ADRs still require national approval.
- [x] The construction identifier service reserves non-overlapping center/year ranges under database locks, adds a check character, accepts controlled offline ranges, and enforces unique collection identifiers.
- [~] Donor → episode → original quarantined container → specimens → labels is implemented and tested. Test orders, components, storage, dispatch, hospital issue, and transfusion links are Phase 7–10 entities and must extend this identifier without replacing it.
- [x] Code 128-B labels are generated, rendered no-store, printed and scan-applied at chair-side; collection cannot start until every current label is applied.
- [x] Template/version, symbology, printer, print count, operator, time, replacement reason, voided label, and replacement provenance are retained.
- [x] Mismatches, duplicate identifiers, unapplied labels, voided labels, and incomplete replacement chains block progress. Relabeling after collection starts is prohibited.
- [x] Expiring positive identity is rechecked before preparation, label application is scan-matched, specimen collection is scan-matched, and specimen handoff is recorded.

### COL-OPS — Collection and donor-care workflow

Priority: Must. Safety: Critical.

- [x] Checked-in appointments feed the controlled screening and ready-for-collection queues; the backend also supports an explicitly authorized walk-in episode.
- [x] Active account, unresolved duplicate, current identity, same-day eligible screening, deferral, donation interval, center authority, center state, appointment reuse, and daily capacity are revalidated inside the collection transaction.
- [x] Collection records include method, bag configuration/lot, optional device, planned/measured volume, start/end, outcome, staff, source mode, reactions, aftercare, acknowledgement, specimens, and handoffs.
- [x] One episode and one donation identifier represent the collection; the legacy whole-blood compatibility unit is explicitly transitional and never treated as the permanent component model.
- [x] The original collection container is created in quarantine and successful completion creates only a `collected` compatibility unit at a quarantine location; available inventory is never incremented.
- [x] Failed, interrupted, under-volume, and over-volume outcomes remain explicit and do not create false usable stock.
- [x] Aftercare, donor acknowledgement, reaction treatment/referral/follow-up, next eligibility, and a generic private after-visit notification are transactionally recorded/queued without exposing clinical details.

### COL-OFF — Mobile collection and offline controls

Priority: Must where connectivity is unreliable. Safety: Critical.

- [!] Operations, privacy, security, and clinical owners must approve the minimum field dataset, campaign/team assignment, device baseline, retention window, and loss/wipe procedure before production use.
- [~] Laravel provides assigned devices, one-time credentials, encrypted received payloads, non-overlapping expiring identifier batches, idempotent receipts, status/conflict queues, rejection with retained evidence, immediate server revocation, and batch revocation. Protected on-device storage, barcode capture, and physical remote wipe belong to the separately owned field client and remain blocked until approved.
- [x] Reconciliation reruns authoritative active-account, duplicate, identity, screening, deferral, interval, center, capacity, identifier, label, specimen, and outcome checks rather than trusting the offline decision.
- [x] A synchronized collection can create only quarantined compatibility stock; it cannot set laboratory release or available inventory state.
- [x] Numbered no-store downtime forms, identifier custody, received/conflict/reconciled/rejected states, retry, and auditable resolution are implemented.

### Phase 6 completion gate

- [x] Automated traceability links donor → collection → quarantined container → every current bag/specimen label and blocks mismatches or incomplete replacement chains.
- [x] Deferred, unresolved-duplicate, ineligible, inactive, foreign-center, expired-identity, or unconfirmed donors cannot be collected.
- [x] Offline idempotency, duplicate identifiers, authoritative conflicts, retry/rejection, revocation, and evidence retention are tested without deleting the encrypted receipt.
- [x] Phase 6 clinical-control, donor-care, authorization, locked identifier reservation, barcode, label, specimen, device, offline, audit, workspace, and quarantine tests pass; visible 1600×900 browser evidence is recorded in `docs/evidence/phase-6-donor-journey-qa/`.

## Phase 7 — Laboratory, quarantine, quality control, and release

### LAB-CATALOG — Approved laboratory master data

Priority: Must. Safety: Critical.

- [ ] Approve test catalog, required TTI screening, blood-group methods, confirmatory algorithms, result codes, interpretation rules, units, reference values, instruments, reagents, controls, and laboratories authorized to perform each test.
- [ ] Version every test algorithm and release criterion with effective dates and approvals.
- [ ] Maintain reagent/consumable catalog, lot, expiry, receipt, stock, storage, validation, and recall state.
- [ ] Maintain analyzer/equipment registry, calibration, maintenance, downtime, and interface state.

### LAB-SAMPLE — Specimen reception and testing

Priority: Must. Safety: Critical.

- [ ] Receive specimens by barcode with collection-container and donation-identifier match.
- [ ] Record rejection, recollection, missing specimen, damaged label, quantity issue, and handoff exception.
- [ ] Create test orders from approved rules; no caller manually marks screening complete.
- [ ] Record test run, instrument/method, reagent/control lots, operator, start/end time, raw/result values, validity, repeat, discrepancy, and comments.
- [ ] Integrate analyzers through validated interfaces where approved; retain manual-entry second checks when integration is unavailable.
- [ ] Track turnaround time, invalid runs, repeats, reagent shortages, interface failures, and pending work.

### LAB-QC — Laboratory quality control

Priority: Must. Safety: Critical.

- [ ] Record internal QC and prevent patient/donation-result use from failed or missing controls according to policy.
- [ ] Track EQA participation, results, corrective actions, and overdue cycles.
- [ ] Record deviations, nonconformities, instrument failures, reagent recalls, and affected donations/components.
- [ ] Require competency for staff performing or approving specific methods.

### REL-QUAR — Hard quarantine

Priority: Must. Safety: Critical.

- [ ] All original units and derived components remain physically and digitally quarantined until release criteria are complete.
- [ ] Quarantined, incomplete, reactive, discrepant, failed-QC, expired, recalled, unlabelled, or unresolved-excursion components do not contribute to available stock.
- [ ] Storage locations clearly distinguish quarantine, released, rejected, recalled, and investigation stock.
- [ ] Status transitions cannot jump directly from collected/testing to available.

### REL-AUTH — Result verification and authorized release

Priority: Must. Safety: Critical.

- [ ] Verify every required result and its run/control context before interpretation.
- [ ] Apply the approved complete-test and component-release rules on the authoritative service.
- [ ] Record release decision, criteria version, tests evaluated, approver, independent approver where required, time, reason, exceptions, and electronic signature.
- [ ] Block the same person from being the only tester, verifier, and releaser where separation is required.
- [ ] Support confirmed rejection, repeat testing, investigation, discard, donor counselling/referral, and look-back triggers.
- [ ] Emergency override cannot convert an unsafe or untested donation into routine released stock.

### Phase 7 completion gate

- [ ] Attempted release with missing, reactive, discrepant, invalid, failed-QC, expired, recalled, or excursion-affected data is blocked.
- [ ] Laboratory and quality owners approve algorithms and validation evidence.
- [ ] End-to-end sample, result, release, rejection, repeat, audit, and authorization tests pass.
- [ ] A release drill proves every released component can show complete test and approval evidence.

## Phase 8 — Components, inventory, cold chain, and logistics

### CMP-MODEL — Component production and lineage

Priority: Must. Safety: Critical.

- [ ] Approve the national component/product catalog, codes, production methods, additive solutions, volumes, storage conditions, shelf lives, labels, and quality criteria.
- [ ] Preserve the legacy blood-unit record as compatibility data while introducing a controlled original-container and derived-component model.
- [ ] One donation identifier may produce zero or more components; each component has a unique product identifier and parent-child lineage.
- [ ] Record processing event, method/device, operator, time, yields, splits, pools, modifications, QC samples/results, deviations, and final label.
- [ ] Block orphan components and lineage gaps.

### INV-COMP — Component-level inventory and FEFO

Priority: Must. Safety: Critical.

- [ ] Track component type, ABO/Rh, special attributes, release state, center, storage device/location, expiry, reservation, allocation, issue, dispatch, receipt, return, disposal, recall, and investigation hold.
- [ ] Allocate FEFO-compatible stock by approved rules while allowing authorized exceptions with reason.
- [ ] Prevent double allocation and release stale reservations automatically or through controlled review.
- [ ] Calculate available, reserved, allocated, in-transit, quarantine, held, recalled, expired, and discarded quantities from authoritative component states.
- [ ] Provide reconciliation between component records, inventory aggregates, physical counts, transfers, issues, returns, and adjustments.
- [ ] Manual adjustments require reason, evidence, actor, independent approval where configured, and no negative stock.

### INV-EXP — Expiry, return, and disposal

Priority: Must. Safety: High.

- [ ] Scheduler identifies expiry risk and expires eligible components without deleting traceability.
- [ ] Returned components require time/temperature/package/chain-of-custody assessment before restocking.
- [ ] Disposal records method, reason, quantity, witness/approval, time, location, and safe evidence.
- [ ] Wastage reporting distinguishes expiry, testing failure, collection failure, processing loss, cold-chain excursion, damage, inappropriate request, return failure, and other approved reasons.

### CC-EQUIP — Cold-chain equipment and telemetry

Priority: Must. Safety: Critical.

- [ ] Register refrigerators, freezers, platelet storage, transport boxes, data loggers, generators, alarms, calibration, maintenance, capacity, location, and responsible staff.
- [ ] Ingest or record continuous temperature data with device identity and synchronization state.
- [ ] Configure alarms, acknowledgement, escalation, backup storage, and time-to-response targets.
- [ ] Open an excursion case that automatically identifies and holds potentially affected components.
- [ ] Quality staff investigates duration/range/product impact and records disposition and CAPA before release/restocking.

### LOG-TRANSFER — Center-to-center transfer

Priority: Must. Safety: High.

- [ ] Transfer request records shortage/surplus reason, source, destination, components, urgency, requester, and approval.
- [ ] Source confirms reservation, pack-out, temperature device, package seal, dispatcher, vehicle/courier, departure, and chain of custody.
- [ ] Destination confirms receipt time, seal/package condition, temperature evidence, component count, discrepancies, and acceptance/hold/rejection.
- [ ] Stock moves only after approved state transitions; lost, delayed, damaged, or excursion consignments open incidents.

### LOG-DISPATCH — Hospital dispatch and proof of delivery

Priority: Must. Safety: Critical.

- [ ] Pack only issued/authorized components against a valid hospital request/allocation.
- [ ] Record route, ETA, courier/vehicle, package, logger, chain-of-custody handoffs, delivery status, and proof of receipt.
- [ ] Reconcile every dispatched component as received, returned, lost, discarded, transfused, or under investigation.

### Phase 8 completion gate

- [ ] Parent-child lineage drill accounts for every component from one donation.
- [ ] Inventory reconciliation has no unexplained component or balance difference.
- [ ] FEFO, reservation contention, transfer, return, expiry, disposal, alarm, excursion, and dispatch tests pass.
- [ ] A cold-chain drill proves affected stock is found and held quickly.

## Phase 9 — Hospital requests, compatibility, issue, and transfusion

### HSP-REG — Hospital and service registry

Priority: Must. Safety: High.

- [ ] Register approved hospitals, blood banks, wards/services, contacts, capabilities, operating hours, request routes, integration identifiers, and service status.
- [ ] Assign hospital users and limit them to their organization and duties.
- [ ] Define minimum necessary patient identity and privacy policy before implementation.

### HSP-REQ — Electronic blood request

Priority: Must. Safety: Critical.

- [ ] Record patient reference, hospital/ward, clinician, diagnosis/indication, haemoglobin and relevant observations, active bleeding, urgency, component, quantity, requested time, and approved attachments/notes.
- [ ] Validate completeness and display approved patient-blood-management guidance without replacing clinical judgment.
- [ ] Require an override reason for requests outside approved guidance.
- [ ] Show request state, review owner, shortages, alternatives, partial fill, cancellation, and timestamps.
- [ ] Prevent telephone/paper requests from disappearing by providing controlled downtime capture and later reconciliation.

### XMT-COMPAT — Patient specimen and compatibility

Priority: Must. Safety: Critical.

- [ ] Positively identify the patient and link the patient specimen to the request.
- [ ] Record ABO/Rh confirmation, antibody screening/identification, compatibility/crossmatch, method, instrument/reagent/control context, operator, reviewer, result, validity window, and exceptions.
- [ ] Block incompatible, expired, recalled, unreceived, unapproved, or wrong-patient components.
- [ ] Define emergency-release workflow with named clinical authorization, reason, acknowledgement of risk, selected component, and retrospective completion.

### HSP-ALLOC — Allocation, issue, and receipt

Priority: Must. Safety: Critical.

- [ ] Allocate compatible FEFO components and prevent double allocation.
- [ ] Final issue check confirms request, patient, component, release, compatibility/emergency authorization, expiry, label, package, and staff.
- [ ] Record hospital receipt, receiving officer, time, condition, temperature evidence, discrepancy, and acceptance/hold.

### TRF-BEDSIDE — Bedside verification and transfusion outcome

Priority: Must. Safety: Critical.

- [ ] Bedside verification confirms right patient, right component, right request, right time, expiry, compatibility/emergency authorization, and staff.
- [ ] Record start, observations required by policy, interruptions, completion, volume, outcome, staff, and unused component disposition.
- [ ] Close donor-to-recipient traceability only after transfused, returned, discarded, or other approved final disposition is recorded.
- [ ] Unreported outcomes appear in an overdue reconciliation queue.

### Phase 9 completion gate

- [ ] Wrong-patient, wrong-component, incompatible, expired, recalled, unissued, and duplicate-transfusion attempts are blocked.
- [ ] Hospital request, compatibility, emergency release, allocation, issue, dispatch, receipt, bedside, and outcome tests pass.
- [ ] One end-to-end pilot traces a donor through an actual or approved simulated recipient workflow.
- [ ] Hospital clinical and blood-bank owners approve the workflow.

## Phase 10 — Haemovigilance, recall, quality management, and clinical governance

### HV-DONOR — Donor reactions

Priority: Must. Safety: High.

- [ ] Record immediate and delayed donor reactions, severity, treatment, referral, outcome, follow-up, and implications for future eligibility.
- [ ] Escalate serious events and link them to collection staff, center, equipment, supplies, and procedures without blaming individuals prematurely.

### HV-RECIP — Recipient adverse events

Priority: Must. Safety: Critical.

- [ ] Record suspected transfusion reactions, event type, symptoms, time, component, patient/request, staff, immediate action, samples/tests, investigation, classification, imputability, outcome, and reporting state.
- [ ] Notify the responsible hospital, NBTS quality/haemovigilance, laboratory, and national authority according to severity.

### HV-RECALL — Recall and look-back

Priority: Must. Safety: Critical.

- [ ] Open recall/look-back from later donor information, reactive/changed test result, equipment/reagent concern, production deviation, labelling error, cold-chain incident, or other approved trigger.
- [ ] Identify all donations, specimens, components, locations, transfers, hospitals, recipients, returns, discarded units, and unaccounted items in both directions.
- [ ] Record actions, notification attempts, component recovery/disposition, patient follow-up, regulator communication, decision authority, deadlines, and closure approval.
- [ ] Measure trace completion time and unresolved recipients/components.

### QMS-CAPA — Deviations, investigation, and CAPA

Priority: Must. Safety: Critical.

- [ ] Record deviation/nonconformity, containment, affected records, root-cause analysis, correction, corrective action, preventive action, owner, due date, effectiveness check, and closure.
- [ ] Connect repeated deviations and audit findings to trend analysis.
- [ ] Prevent closing critical CAPA without evidence and quality approval.

### QMS-SOP — SOPs, documents, EQA, audits, and competency

Priority: Must. Safety: High.

- [ ] Version and approve SOPs, forms, checklists, work instructions, labels, clinical rules, and training materials with effective/retired dates.
- [ ] Link workflow actions to the active SOP/rule version.
- [ ] Track staff training, task competency, reassessment, expiry, and retraining after critical change.
- [ ] Track internal audits, EQA, findings, actions, and accreditation readiness.
- [ ] Support active hospital transfusion committees with utilization, emergency release, reaction, wastage, and education review.

### Phase 10 completion gate

- [ ] Adverse-event, recall, look-back, deviation, CAPA, SOP, competency, audit, and EQA tests pass.
- [ ] A simulated recall locates every affected component and recipient or records an explicit unresolved exception.
- [ ] Quality/haemovigilance owners approve investigation and closure authority.

## Phase 11 — Data governance, interoperability, security, resilience, and service management

### SEC-PRIV — Data protection, consent, and retention

Priority: Must. Safety: High.

- [ ] Complete processing inventory, controller/processor responsibilities, DPO oversight, DPIAs, lawful purposes, data minimization, vendor controls, breach response, and rights handling.
- [ ] Version consent and privacy notices; store communication preferences and safe opt-out processing.
- [ ] Define retention and secure archival schedules for donor, laboratory, component, recipient, audit, adverse-event, and traceability records according to approved law/policy.
- [ ] Prevent unauthorized deletion of records required for traceability or safety.
- [ ] Protect sensitive exports and record purpose, approver, recipient, scope, and expiry.

### INT-STD — Standards and interoperability

Priority: Must for national scale. Safety: High–Critical by interface.

- [ ] Approve local interoperability architecture and Ministry profiles before generic implementation.
- [ ] Assess and approve FHIR profiles/terminology for facilities, patients, requests, specimens, observations, tasks, products, issues, transfusions, and audit events.
- [ ] Approve ISBT 128 or national barcode/product coding equivalent.
- [ ] Build API gateway/integration engine with acknowledgements, retries, idempotency, sequence checks, reconciliation dashboard, and dead-letter queue.
- [ ] Integrate approved HMIS, laboratory analyzers/LIS, DHIS2/reporting, identity, messaging, temperature sensors, GPS/fleet, and other systems without direct database access.
- [ ] Failed interfaces must not silently lose orders, results, temperature readings, or outcomes.

### SEC-ACCESS — Security operations

Priority: Must. Safety: Critical for privileged and clinical access.

- [x] Fortify, Sanctum, MFA, passkeys, permissions, center scope, inactive-account enforcement, and audit foundation exist.
- [ ] Require MFA for privileged, laboratory approval, release, recall, configuration, backup, and other approved high-risk roles.
- [ ] Implement periodic access review, least privilege, separation conflict report, session/device management, anomalous access/export monitoring, and incident response.
- [ ] Encrypt data in transit, at rest, in backups, and on offline devices with managed secrets/keys and certificate renewal.
- [ ] Segment public, application, integration, database, backup, monitoring, and device trust boundaries.
- [ ] Add immutable/offline-protected backups and ransomware recovery exercises.

### DR-BCP — Availability, downtime, backup, and disaster recovery

Priority: Must. Safety: Critical.

- [x] Private local development backup and isolated restore proof exist.
- [ ] Complete business-impact analysis and approve RTO/RPO by service.
- [ ] Implement encrypted automated database and media/document backups to approved off-site storage with retention and access controls.
- [ ] Verify backup existence, size, age, checksum/readability, and restore capability; alert on failure.
- [ ] Maintain approved downtime procedures, forms, identifier controls, manual chain of custody, later reconciliation, and activation within the agreed target.
- [ ] Conduct quarterly restore tests and an annual full disaster-recovery exercise, subject to final policy.
- [ ] Record recovery point, recovery time, operator, validation checks, exceptions, reopening approval, and post-incident CAPA.

### OPS-SLA — Monitoring, support, and escalation

Priority: Must. Safety: High.

- [ ] Define incident severity, acknowledgement, workaround, restoration, permanent resolution, communication, owner, and escalation targets.
- [ ] Monitor service health, queues, jobs, interfaces, database, storage, sensors, certificates, security events, backup age, and center connectivity.
- [ ] Provide support-case management with center, service, impact, status, workaround, root cause, SLA, communication, and recurrence links.
- [ ] Maintain a 24/7 path for critical unsafe-release, traceability, national outage, wrong-identification, and cold-chain incidents.

### GOV-CHANGE — Validated configuration and change control

Priority: Must. Safety: Critical.

- [ ] Classify clinical/safety, privacy/data, operational, and infrastructure changes.
- [ ] Clinical changes to eligibility, tests, expiry, labels, quarantine, release, compatibility, or emergency rules require clinical, laboratory, quality, and validation approval.
- [ ] Privacy/data changes require DPO/data-governance/legal review.
- [ ] Maintain versioned configuration, test environment, regression evidence, migration plan, rollback, release notes, training impact, and effective date.
- [ ] Prevent direct production edits to safety rules except controlled emergency procedure with retrospective review.
- [ ] Establish product/change-control board and named clinical safety officer.

### Phase 11 completion gate

- [ ] Privacy, retention, access, encryption, interface, downtime, backup, restore, incident, monitoring, SLA, and change-control tests/drills pass.
- [ ] No critical interface can fail silently.
- [ ] Recovery and downtime preserve identification, quarantine, release, issue, and traceability controls.
- [ ] DPO, ICT/security, quality, clinical, operations, and governance owners approve readiness.

## Phase 12 — User experience, communication, reporting, PDFs, and managed content

### UX-STAFF — Workflow-driven staff and administrator account

Priority: Must for the relevant workflow. Safety: High.

- [ ] Overview and selected-center context.
- [ ] Donor reception: scan/search, duplicate review, registration, identity confirmation, and profile.
- [ ] Appointments: pending, today, check-in, no-show, reschedule, cancellation, and exceptions.
- [ ] Screening/counselling: queue, questionnaire, eligibility, deferral, referral, and re-entry.
- [ ] Collection: donor confirmation, identifiers, label printing/scanning, collection, specimens, reactions, and handoff.
- [ ] Laboratory: specimen reception, tests, runs, QC, repeats, discrepancy, result review, quarantine, and release.
- [ ] Components: processing, lineage, labels, QC, and production deviations.
- [ ] Inventory/storage: FEFO, reservations, locations, devices, transfers, returns, expiry, disposal, and reconciliation.
- [ ] Logistics: pack-out, route, custody, temperature, delivery, and receipt exceptions.
- [ ] Hospital: requests, compatibility, allocation, emergency release, issue, receipt, transfusion outcome, and returns.
- [ ] Quality/haemovigilance: reactions, incidents, recalls, look-back, deviations, CAPA, SOPs, competency, EQA, and audits.
- [ ] Response/engagement: low-stock alerts, campaigns, targeted communication, notifications, loyalty, and feedback.
- [ ] Intelligence: national, center, laboratory, inventory, hospital, quality, safety, finance/cost, security, and service reports.
- [ ] Administration: hierarchy, centers, departments, users, assignments, permissions, master data, configuration, integrations, audit, recovery, and change control.
- [ ] Account: profile, language, appearance, password, 2FA, passkeys, sessions/devices, and security history.
- [ ] Every page supports appropriate search, filters, sorting, pagination, states, error recovery, accessibility, and center/department scope.

### MSG-OMNI — Notifications and donor engagement

Priority: Should after core safety; Must for approved operational alerts.

- [x] Donor notification inbox and device-token registration foundation exist.
- [ ] Add after-commit outbox, retries, provider status, failure visibility, idempotency, preferences, consent, quiet hours, and safe templates.
- [ ] Operational alerts: appointment, screening follow-up, donation aftercare, critical stock, laboratory exception, cold-chain alarm, request delay, dispatch/receipt, adverse event, recall, outage, security, and backup failure.
- [ ] Segment donor communication by approved location, blood group, eligibility date, language, channel, previous response, and consent without exposing sensitive information.
- [ ] Support SMS/USSD/assisted access where approved; do not assume smartphones or continuous data.
- [ ] Recognition remains non-coercive and must not become payment for blood.

### RPT-KPI — Reports, analytics, and KPI definitions

Priority: Must for governance; forecasting is Should.

- [ ] Create an approved KPI dictionary with numerator, denominator, exclusions, source, owner, frequency, target, and data-quality checks.
- [ ] Report donor conversion, deferral, re-entry, repeat donation, waiting time, reactions, and satisfaction.
- [ ] Report collection, usable yield, test turnaround, invalid/repeat runs, release time, rejection, component yield, stock days, FEFO exceptions, expiry, discard reasons, transfers, request fill rate, compatibility time, issue/delivery time, transfusion outcomes, reactions, recalls, CAPA, EQA, competency, downtime, SLA, security, and recovery.
- [ ] Balance collection totals with safety, utilization, wastage, inappropriate use, and adverse-event indicators to prevent KPI gaming.
- [ ] Add de-identified analytics warehouse and reproducible national dashboards after transactional controls are stable.
- [ ] Add demand forecasting and targeted campaign triggers only after data quality and component-level demand are proven.

### PDF-DOC — PDFs and exports

Priority: Must where operational/legal evidence requires; otherwise Should.

- [ ] Donor card/summary, appointment confirmation, screening/deferral summary, donation acknowledgement/certificate, collection/specimen label packs, laboratory worksheet/results, release record, component traceability, inventory/expiry/disposal, transfer/dispatch/receipt, hospital request/compatibility/issue, transfusion record, adverse event, recall/look-back, CAPA, audit, backup/DR, and national/center summary PDFs.
- [ ] Versioned snapshot, locale labels, stable identifiers/codes, source period, document ID, issue time, verification context, access control, audit, and reproducible output.
- [ ] Large exports run through queues, expire safely, and do not expose unauthorized donor/patient data.

### WEB-CONTENT — Public information and legal content

Priority: Must for privacy/legal content; other management items continue from Phase 4.

- [x] Public website foundation and primary pages exist.
- [ ] Add approved privacy policy, data-protection notice, terms, cookie statement where applicable, and complaint/rights process.
- [ ] Add controlled content workflow for centers, schedules, campaigns, news, publications, FAQs, leadership/governance, regional contacts, approved impact indicators, and mobile-store links.
- [ ] Public stock/impact information must be aggregated, approved, dated, and safe; do not expose sensitive rare-stock or donor details.

### MOB-COMPLETE — Flutter and field-device completion

Priority: Must for supported donor and field workflows.

- [!] Install approved Flutter tooling in the verification environment or use CI/device lab.
- [ ] Complete Android end-to-end donor journey and supported social authentication.
- [ ] Configure and verify iOS before claiming support.
- [ ] Prove push delivery, token invalidation, network environments, accessibility, error states, and offline behavior on supported devices.
- [ ] Add field barcode/device workflows only after security, identifier, and offline specifications are approved.

### Phase 12 completion gate

- [ ] Each role completes allowed workflows in English and Kiswahili and is blocked from forbidden actions.
- [ ] Desktop 1600×900, tablet, mobile, barcode/printer, and approved device QA pass.
- [ ] Reports reconcile with authoritative source records and approved KPI definitions.
- [ ] PDFs/exports are authorized, reproducible, localized, audited, and privacy-safe.

## Phase 13 — Discovery, pilot validation, controlled rollout, and sustainability

### ROLLOUT-0 — Foundation and discovery

Priority: Must before safety-critical schema build.

Indicative planning range: 6–10 weeks; not contractual.

- [ ] Map actual collection, laboratory, component, storage, logistics, hospital, transfusion, adverse-event, recall, downtime, and governance workflows at representative sites.
- [ ] Inventory current centers, staff, volumes, identifiers, forms, SOPs, analyzers, reagents, equipment, storage, sensors, routes, hospitals, connectivity, power, integrations, laws, contracts, and budgets.
- [ ] Establish baseline KPIs, risk register, data dictionary, master-data ownership, safety case, approved target process, pilot scope, and prioritized backlog.
- [ ] Resolve policy decisions: center taxonomy, identifier/label standard, component catalog, tests/algorithms, shelf lives, release authority, compatibility/emergency rules, patient data, retention, offline mode, RTO/RPO, integrations, and service levels.

### ROLLOUT-1 — Core safety pilot

Priority: Must.

Indicative planning range: 4–6 months after approved discovery; not contractual.

- [ ] Pilot the complete chain at a controlled set of sites: donor → screening → collection/identifier → specimens → laboratory/QC → quarantine/release → components → inventory → one hospital request/compatibility/issue → dispatch/receipt → transfusion outcome.
- [ ] Complete data migration/reconciliation, training, competency, SOP deployment, hardware, labels/barcodes, test environment, validation, downtime drill, restore test, support readiness, and traceability/recall simulation.
- [ ] Exit only with no unresolved critical defects, approved validation evidence, acceptable data quality, user acceptance, and clinical/quality sign-off.

### ROLLOUT-2 — Controlled regional scale

Priority: Should after pilot acceptance.

Indicative planning range: 4–8 months; not contractual.

- [ ] Expand offline/mobile collection, hospital requests, transport, cold-chain telemetry, haemovigilance, interfaces, inventory balancing, support, and monitoring.
- [ ] Onboard centers/hospitals by readiness criteria rather than calendar alone.
- [ ] Compare safety, adoption, request fill, expiry, turnaround, incident, downtime, and support KPIs before each expansion wave.

### ROLLOUT-3 — National optimization

Priority: Should/Could after stable scale.

Indicative planning range: 6–12 months; not contractual.

- [ ] National command dashboards, cross-region balancing, forecasting, advanced analytics, additional integrations, public aggregate indicators, donor segmentation, and continuous-improvement cycles.
- [ ] Confirm sustainable annual operating budget for infrastructure, devices, sensors, connectivity, messaging, security, backups, support, training, calibration, maintenance, validation, and renewals.
- [ ] Complete vendor-exit plan: source/data ownership, open exports, documentation, test suites, deployment automation, local administrators, handover drill, and independent recovery capability.

### Final completion gate

- [ ] Every `Must` requirement is complete or formally rejected/accepted out of scope by the accountable authority with documented risk acceptance.
- [ ] `docs/achievement.md` contains direct evidence for every delivered requirement.
- [ ] End-to-end donor-to-recipient traceability and recipient-to-donor look-back pass within approved targets.
- [ ] Unsafe release, wrong identification, incompatible issue, wrong-patient transfusion, cold-chain excursion, silent interface failure, outage, backup loss, and uncontrolled change scenarios are tested.
- [ ] Clinical, laboratory, quality, operations, hospital, DPO, ICT/security, finance, procurement, and executive owners sign off.
- [ ] Recovery, rollback, support, training, competency, recurring financing, and vendor exit are proven.
- [ ] No unresolved critical safety, security, privacy, traceability, data-integrity, resilience, or accessibility issue remains.
