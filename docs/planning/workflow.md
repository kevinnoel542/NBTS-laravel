# NBTS system workflow

Last updated: 2026-08-13

## Purpose

This document is the target operating model for NBTS-NEW. It merges the useful behavior from the previous NBTS workspace with the new Laravel 13, Fortify, Livewire 4, Flux UI, Tailwind CSS 4, Pest 5, Firebase, and Flutter foundation.

## Document authority and implementation boundary

This document defines the target operating model. It intentionally distinguishes:

- **Verified current foundation:** donor accounts, reception/search and duplicate review, expiring identity confirmation, versioned construction screening, checked-in donor queues, locked construction collection identifiers, Code 128 label/scan controls, quarantined collection containers, specimens/handoffs, collection outcomes, donor reactions, encrypted offline receipts/reconciliation, public/mobile discovery, appointments, donor card, donation history, blood-group verification, auditable blood-unit/inventory transitions, center/assignment scoping, role-aware dashboards, notification delivery, public website, and stable API contracts recorded in `docs/evidence/achievement.md`.
- **Target national extension:** externally approved production donor rules and identifier/barcode standards, laboratory/QC, hard release authority, component production and lineage, component-level inventory, cold chain, hospital requests, compatibility, issue/dispatch/receipt, bedside transfusion, recipient haemovigilance, recall/look-back, quality management, interoperability, production device controls, disaster recovery, and controlled rollout.

Target sections are requirements, not claims of implementation. Clinical, laboratory, quality, hospital, data-protection, legal, Ministry, and operational rules remain subject to formal approval and versioning.

Legacy sources reconciled:

- `PROJECT_OVERVIEW.md`
- `MAIN_WORKFLOWS.md`
- `DATA_AND_DATABASE.md`
- `ADMIN_PANEL_PAGES.md`
- `PUBLIC_WEBSITE_PAGES.md`
- `MOBILE_APP_API.md`
- `MOBILE_BACKEND_SYNC_CHECKLIST.md`
- `KNOWN_IMPLEMENTATION_NOTES.md`
- `Blood Donation Platform — System Requirements.md`
- The previous public-site task tracker and Flutter achievement log.

Where a legacy document and the current direction differ, this document is the target. Existing API field names remain compatibility requirements until both Laravel and Flutter are migrated together.

## System boundaries

### Public website

The website provides trusted NBTS information, centers, campaigns, schedules, news, publications, impact data, customer service, and verified mobile-app links. Visitors do not need an account.

### Donor mobile application

Donors use Flutter to register, authenticate, manage their profile, hold a digital donor card, check eligibility, find centers and campaigns, book appointments, view donation history and loyalty, and receive notifications.

### Staff and administrator web account

Center staff, center managers, NBTS administrators, and super administrators use a Fortify-secured Livewire/Flux account. Navigation follows the operational workflow and hides unauthorized modules. Staff can switch between English and Kiswahili.

### Laravel backend

Laravel owns authentication, authorization, API contracts, business transactions, audit logging, queues, scheduling, reporting, document generation, notification orchestration, and data integrity.


### National coordination and command layer

NBTS national users coordinate approved policy, master data, quality, inventory balancing, emergency allocation, donor engagement, haemovigilance, analytics, service support, data governance, and rollout across centers. National visibility does not remove center accountability or clinical separation of duties.

### Blood-center operating layer

Each approved center has a defined type, services, departments, staff assignments, storage/work locations, equipment, routes, hospitals served, capacity, operating hours, downtime method, and escalation contacts. A center can perform only functions approved for its type and current status.

### Hospital and transfusion layer

Hospitals use an approved portal or interoperable local system for clinical requests, patient/specimen identification, compatibility, allocation, issue/receipt, bedside verification, transfusion outcomes, returns, reactions, and look-back. Hospital access is organization-scoped and minimum-necessary.

### Integration, devices, and field operations

The platform connects approved laboratory analyzers/LIS, HMIS/DHIS2, barcode printers/scanners, temperature sensors, GPS/fleet tools, messaging providers, identity services, and offline field devices through validated interfaces. No integration writes directly to authoritative tables.

## Roles and operating scope

Permissions, center/hospital assignments, department, competency, active status, and separation-of-duties rules decide access. Role names are convenient permission bundles, not the only authorization boundary.

### Current canonical compatibility roles

| Role | Primary responsibility | Data scope |
| --- | --- | --- |
| Public visitor | Learn, locate services, read approved content, contact NBTS | Published public data |
| Donor | Manage the personal donor journey through Flutter | Own records and public data |
| Center staff | Compatibility role for permitted center operations | Assigned active centers and permitted actions |
| Center manager | Supervise assigned-center operations, staff, stock, incidents, and reports | Assigned active centers |
| NBTS admin | National operational administration without unrestricted infrastructure authority | Approved national operational data |
| Super admin | Technical security, configuration, recovery, and full administration | Entire system, but not automatic clinical release authority |

The five authenticated compatibility roles (`super_admin`, `nbts_admin`, `center_manager`, `center_staff`, and `donor`) remain in place during migration. They are not the final operating-role catalogue. The target catalogue contains 26 role profiles:

| No. | Operating layer | Target role profile |
| ---: | --- | --- |
| 1 | Platform | Super administrator |
| 2 | Platform | ICT/security operator |
| 3 | National | National operations administrator |
| 4 | National | National quality/haemovigilance officer |
| 5 | National | National inventory/logistics coordinator |
| 6 | National | National donor engagement/content officer |
| 7 | National | Data-protection/governance officer |
| 8 | National | National auditor/inspector |
| 9 | Blood center | Center manager |
| 10 | Blood center | Reception officer |
| 11 | Blood center | Screening/counselling officer |
| 12 | Blood center | Collection/phlebotomy officer |
| 13 | Blood center | Laboratory technician |
| 14 | Blood center | Laboratory approver/quality officer |
| 15 | Blood center | Component-processing officer |
| 16 | Blood center | Inventory officer |
| 17 | Blood center | Logistics/cold-chain officer |
| 18 | Blood center | Center haemovigilance/quality officer |
| 19 | Blood center | Center read-only auditor |
| 20 | Hospital | Hospital clinician/requester |
| 21 | Hospital | Hospital blood-bank officer |
| 22 | Hospital | Compatibility/crossmatch officer |
| 23 | Hospital | Transfusion nurse/officer |
| 24 | Hospital | Hospital haemovigilance officer |
| 25 | Hospital | Hospital read-only reviewer |
| 26 | Donor | Donor |

### Account and assignment policy

- One person has one identity account. The system must not create duplicate accounts merely because that person works in several roles, departments, centers, or hospitals.
- A staff account may hold multiple scoped assignments. Every assignment records its role profile, organization/center/hospital, department, shift where applicable, effective dates, status, approver, reason, and competency restrictions.
- Users with more than one active assignment select an active role and location context. Navigation, dashboard content, queues, actions, and data scope recalculate from that context.
- Prohibited role combinations and dual-control rules still apply when one person has several assignments. The person who performs a test must not be the sole verifier and releaser of the same component where independent approval is required.
- Technical administrators cannot grant themselves clinical release authority through infrastructure access, and super-administrator access does not imply clinical competency.
- Deactivating an account or assignment removes current access without deleting historical responsibility, audit evidence, or records signed by that person.

During construction, the minimum representative local demo set is five accounts: super administrator, NBTS administrator, center manager, center staff, and donor. These accounts validate the current compatibility boundary; specialized Phase 5 demo identities are added only when their scoped permissions and workflows are implemented. Credentials remain documented only in `docs/security/local-demo-credentials.md` and must never be production credentials.

## Center hierarchy, types, departments, and assignments

Target hierarchy:

`National NBTS → approved zone/region where applicable → blood center → department → work/storage location`

The exact national and regional structure must be approved during discovery. Candidate center types for assessment—not assumed current policy—include full collection/testing/processing centers, collection-only sites/mobile teams, testing/processing hubs, storage/distribution hubs, and hospital blood-bank interfaces.

Each center record controls:

- Stable code, type, status, region/zone, location, contacts, opening hours, capacity, and emergency contacts.
- Approved donation methods, laboratory tests, component processing, storage, issue/distribution, hospitals served, mobile teams, and downtime capability.
- Departments, storage/work locations, equipment, vehicles/routes, stock thresholds, and responsible owners.
- Staff assignment with department/profile, shift, effective dates, approver, active state, and competency restrictions.

## Overview and work-queue workflow

The dashboard is one role-aware Laravel shell, not 26 duplicated pages. It uses shared components and one visual system while composing the data, queues, actions, and explanations permitted by the active role and location assignment. The 26 target profiles map to 13 staff dashboard configurations plus one donor mobile dashboard:

| Dashboard configuration | Roles served | Primary content |
| --- | --- | --- |
| 1. System control | Super administrator and ICT/security operator | Availability, failed jobs, integration/dead-letter backlogs, security alerts, audit integrity, backup age, restore tests, certificate expiry, devices, and support incidents |
| 2. National operations | National operations administrator | National stock, collections, usable yield, shortages, center performance, service exceptions, and emergency coordination |
| 3. National quality and governance | National quality/haemovigilance, data-protection/governance, and national audit profiles | Adverse events, recalls, CAPA, audit findings, privacy controls, overdue reviews, and evidence queues, with actions restricted per profile |
| 4. National inventory and logistics | National inventory/logistics coordinator | Component/group/region stock, days of supply, transfers, expiry, cold-chain exceptions, hospital demand, and balancing decisions |
| 5. Engagement and content | National donor engagement/content officer | Campaigns, eligible audiences, communication delivery, approval queues, publications, schedules, and content performance |
| 6. Center management | Center manager | Today’s donors, staff coverage, department queues, stock, reservations, expiry, incidents, alarms, hospital demand, and unresolved exceptions |
| 7. Reception | Reception officer | Arrivals, appointments, walk-ins, donor search, identity/duplicate checks, registration, and check-in |
| 8. Screening and counselling | Screening/counselling officer | Donors awaiting screening, eligibility decisions, deferrals, re-entry reviews, counselling, and overdue appointments |
| 9. Collection | Collection/phlebotomy officer | Cleared donors, collections in progress, identifiers/labels, reactions, incomplete records, specimens, and handoffs |
| 10. Laboratory and components | Laboratory technician, laboratory approver/quality officer, and component-processing officer | Specimens, tests, QC, repeats, discrepancies, quarantine, independent release queues, component processing, lineage, and yields |
| 11. Center inventory and logistics | Inventory officer and logistics/cold-chain officer | Available/reserved stock, FEFO, expiry risk, locations, transfers, dispatch/receipt, temperature excursions, and reconciliation |
| 12. Center quality and haemovigilance | Center haemovigilance/quality officer and center read-only auditor | Incidents, donor/recipient reactions, recalls, deviations, investigations, CAPA, evidence, and overdue actions |
| 13. Hospital operations | All six hospital profiles | Requests, compatibility, allocation, emergency release, issue, receipt, bedside verification, outcomes, returns, and reactions, filtered to each role’s authority |
| 14. Donor home (Flutter) | Donor | Eligibility, next donation date, donor card, appointments, history, recognition, notifications, centers, and campaigns |

### Shared dashboard composition

- The heading identifies the user’s active role, center/hospital or national scope, and gives a short explanation of that dashboard’s responsibility.
- Users with multiple assignments receive a role/location context switcher; changing it refreshes navigation and dashboard authorization without requiring another account.
- Urgent, unsafe, overdue, and blocked work appears before routine totals. Critical queues show age, SLA, owner, escalation state, and overdue status.
- Use compact connected metric strips and content-sized work panels rather than oversized isolated cards. Hide sections that have no permission or purpose instead of leaving empty gaps.
- Every metric links to the queue or source records that explain it. Decorative totals are prohibited.
- Quick actions are role-appropriate, permission checked, and placed next to the queue they affect. High-risk actions retain reason, confirmation, competency, audit, and independent-approval controls.
- The visual shell, responsive behavior, English/Kiswahili support, focus states, reduced-motion behavior, loading states, and empty/error states remain consistent across every dashboard configuration.

### Verified Phase 5 operating foundation

The additive organization, department, work-location, competency, and effective-dated staff-assignment foundation is implemented. All 26 target profiles and two compatibility-only role codes seed with explicit permissions; 25 staff profiles map to exactly 13 shared Laravel dashboard configurations, while donor access remains mobile/API-only. Active assignment changes are ownership checked and recalculate scope, permissions, navigation, real metrics, queues, and quick actions. The compatibility backfill is dry-run capable, non-destructive, and idempotent.

The five local compatibility accounts are seeded and recorded in `docs/security/local-demo-credentials.md`. The complete Laravel baseline suite passed 213 tests with 2,473 assertions; after visible QA found the final national inventory aggregation defect, the affected dashboard file passed 6 tests with 51 assertions and PHPStan passed with zero errors. Headed 1600×900 browser QA covers all five account boundaries, assignment switching, collapsed navigation, dark/light presentation, overflow, and browser errors. Later clinical panels remain omitted or explicitly unavailable until the affected workflow and external authority are approved.

### Verified Phase 2 operational workspaces

The staff command center now provides compact, center-aware workspaces for donor reception, appointments, eligibility, donations, blood operations, response, engagement, and content. Each workspace has a descriptive heading, workflow tabs, search, role-appropriate status/date filters, clear/reset controls, configurable columns, sorting, pagination, CSV export, and audited record actions. Tables contract to their content so short queues do not create oversized empty cards.

Notification orchestration records one delivery plan per recipient and channel. In-app delivery is native; email, SMS, and push use retryable channel adapters and retain status, attempts, provider identifiers, and safe failure details. External SMS and push default to construction-safe log transports. Setting `PUSH_TRANSPORT=fcm` with approved Firebase credentials activates the Phase 3 HTTP v1 transport without changing the delivery contract.

### Verified Phase 6 donor-to-quarantine foundation

- `Donor reception` provides scoped donor search, signed-card lookup, registration consent/preferences, possible-duplicate review, immutable merge provenance, and expiring identity confirmation.
- `Eligibility & counselling` provides checked-in queues, versioned protocol/rule snapshots, confidential self-exclusion, controlled deferrals, private counselling/referral/re-entry evidence, and generic follow-up notifications that omit sensitive reasons.
- `Collection control` provides ready queues, locked identifiers, Code 128 labels, print/scan/replacement controls, original quarantined containers, required specimens, handoff, outcomes, donor reactions, and private aftercare communication.
- Offline collection provides assigned/revocable devices, expiring server-reserved identifiers, encrypted and idempotent receipts, authoritative reconciliation, conflict/rejection queues, and controlled downtime forms.
- The active thresholds, identifier format, label layout, and offline dataset are construction controls only. Production activation remains blocked until the external approvals recorded in `docs/planning/task.md` are complete.

## Language workflow

- Supported account languages are English (`en`) and Kiswahili (`sw`).
- A user selects a language from authentication/public navigation or account settings.
- The preference persists on the user record and session.
- Navigation, validation, actions, messages, reports, and PDF labels follow the selected language.
- Stored state codes remain stable English machine values; only display labels are translated.
- Managed public content can have English and Kiswahili variants with an explicit fallback when one translation is awaiting publication.
- Audit data records stable action codes so changing locale never changes evidence.

## Identity and access workflows

### Staff/admin password login

1. The user opens the staff sign-in page.
2. Fortify validates the normalized email and password under rate limiting.
3. The system rejects inactive users.
4. If two-factor authentication is enabled, Fortify presents the challenge.
5. The system creates a secure web session.
6. Authorization resolves roles, permissions, and active center assignments.
7. The user enters the first permitted workflow queue, normally Overview.
8. The audit trail records successful and failed security-relevant events without storing secrets.

Current construction decision:

- Staff email verification, verification notices, and resend routes are disabled while the system and local demo accounts are being built.
- Firebase's trusted verified-email claim remains required where the mobile identity-linking workflow states it is required; this is separate from staff web email verification.
- Before pilot or production acceptance, the product and security owners must decide whether staff email verification is re-enabled and update the access tests and operating procedure accordingly.

### Passkey login

1. The browser requests passkey options for the configured relying party and origin.
2. The user completes the platform authenticator challenge.
3. Fortify validates the credential and counter.
4. The inactive-user and authorization checks still apply.
5. The credential's last-used time and security event are recorded.

### Mobile password login

1. A donor registers through `/api/v1/auth/register` with name, optional email, unique phone, confirmed password, self-reported blood group, gender, region, date of birth, and optional device name.
2. Registration creates the donor role/profile and a stable donor ID in one transaction; self-reported blood group remains `user_selected`, never staff verified.
3. The donor later submits email or phone plus password to `/api/v1/auth/login`.
4. Laravel validates the password and requires an active donor account; staff and inactive accounts receive the same generic failure as invalid credentials.
5. Sanctum issues a named, revocable 30-day mobile token. Re-authenticating the same device name replaces its old token.
6. The API returns the same stable `UserResource` representation used by Firebase login.
7. Logout revokes only the presented bearer token.

### Mobile Firebase login

1. Flutter authenticates with an enabled Firebase provider.
2. Flutter sends the Firebase ID token and a recognizable `device_name` to `/api/v1/auth/firebase`.
3. Laravel's Firebase Admin bridge validates signature, issuer, audience, subject, activation time, expiry, and revocation against the credential's configured project (`nbts-d567e`).
4. Laravel links by Firebase UID. First-time linking or account creation by email requires the token's trusted `email_verified` claim; client-submitted UID, provider, email, role, and profile values are not identity evidence.
5. UID/email conflicts, inactive users, and attempts to auto-link staff accounts through the donor app are rejected.
6. Laravel creates the donor role and donor profile only when needed and records a redacted audit event.
7. Sanctum returns a named 30-day token with `donor:read` and `donor:write` abilities; a new login replaces the prior token for the same device name.
8. `GET /api/v1/me` returns the current donor resource and `POST /api/v1/logout` revokes the presented bearer token. Firebase establishes identity but never replaces Laravel authorization.

## End-to-end donor journey

1. A donor downloads the verified Flutter application.
2. The donor registers with password credentials or authenticates through Firebase.
3. Laravel creates the user, donor role, donor profile, language preference, and unique donor ID.
4. The app displays an expiring digital donor-card QR payload.
5. The donor reviews eligibility guidance and current personal eligibility.
6. The donor finds an active blood center, campaign, or collection schedule.
7. The donor books an available appointment or later arrives as a walk-in.
8. The center receives the donor through QR, donor ID, phone, email, or name search.
9. Staff confirms identity and profile details.
10. Staff performs and records the health/eligibility screening.
11. If eligible, collection staff prepare a locked identifier, print and scan-apply every current bag/specimen label, start the episode, collect/handoff required specimens, and record the outcome and donor aftercare.
12. The verified current foundation updates the appointment, donation history, next eligibility, original quarantined container, specimen/label chain, transitional quarantined blood-unit record, reactions, audit trail, and private notifications. The target workflow then continues through laboratory/QC, authorized release, component production, component inventory, hospital issue, transfusion outcome, and haemovigilance.
13. The donor sees updated history and receives appropriate in-app/push/SMS/email communication based on consent and configuration.

## Reception and donor identification

1. Staff opens Donor reception.
2. Staff scans an expiring signed QR payload or searches by donor ID, phone, email, or name.
3. The system shows a privacy-limited identity preview and current operating center; clinical actions remain separate.
4. Staff confirms the correct donor with an approved expiring method before screening or collection. Failed checks are retained for audit and do not grant access.
5. New walk-in donors capture the current privacy-notice version, consent, language, preferred center, and channel preferences before duplicate detection.
6. A likely match blocks creation unless an authorized reason opens a review case. Merge never deletes the source identity: operational history moves, the source sign-in is disabled, and an immutable alias preserves provenance.
7. Approved national-identifier lookup is not active until its authoritative source and data-governance controls are approved.

## Appointment workflow

### Donor booking

1. The donor queries an active center's slots for a date within the configured 90-day booking window.
2. The current compatibility schedule exposes `08:00`, `09:30`, `11:00`, `13:00`, `14:30`, and `16:00`; capacity defaults to one active booking per center/slot and is operator-configurable.
3. The donor chooses an active center and available future time.
4. A transaction locks the center row, then checks capacity, configured time, center status, booking window, and whether the donor already has a `pending` or `confirmed` appointment.
5. The appointment starts as `pending` and an audit record captures the center and scheduled time without copying donor notes.
6. Notification history and channel dispatch will be added after the notification outbox layer exists.

### Donor rescheduling and cancellation

1. Only the owning active donor can reschedule or cancel a `pending` or `confirmed` appointment through a `donor:write` token.
2. Rescheduling locks the destination center and appointment, reruns the same availability rules while excluding the current record, resets status to `pending`, clears prior staff confirmation, and records the previous schedule in audit metadata.
3. Cancellation changes the appointment to `cancelled`, records the cancellation time, and preserves the history.
4. Completed/cancelled appointments and appointments owned by another donor cannot be mutated through donor APIs.

### Staff processing

1. Staff views today's or pending queue for an assigned center.
2. Staff confirms, reschedules, cancels, or checks in the donor with a reason where required.
3. A checked-in appointment enters the eligibility queue.
4. Completion occurs through donation recording, not a disconnected status edit.

### Appointment states

`pending → confirmed → checked_in → completed`

Alternative terminal paths:

- `pending|confirmed → rescheduled`
- `pending|confirmed → cancelled`
- `pending|confirmed → no_show`

Legacy records without the expanded states remain readable until an additive migration and compatibility mapping are deployed.

## Eligibility and deferral workflow

1. Staff starts screening from the donor or checked-in appointment.
2. The system calculates age and displays recent donation/deferral history.
3. Staff records weight, hemoglobin where required, health answers, observations, and notes.
4. The eligibility service evaluates configurable rules:
   - Minimum and maximum age.
   - Minimum weight.
   - Required donation interval, including approved gender-specific intervals.
   - Active temporary or permanent deferrals.
   - Other approved clinical answers.
5. Staff makes the final safety decision.
6. Laravel stores the identity link, immutable screening record, exact protocol/rule/questionnaire versions, stable decision code, actor, center, source mode, and current donor eligibility summary.
7. Confidential self-exclusion always becomes a controlled temporary deferral and does not require an unsafe “eligible” override.
8. A temporary deferral includes a private re-entry date, counselling/referral plan, and a generic notification that never copies the reason into ordinary channels; a permanent deferral has no automatic expiry.
9. Lifting a deferral requires authorization, reason, actor, and reevaluation.

Eligibility codes:

- `eligible`
- `not_yet_eligible`
- `temporarily_deferred`
- `permanently_deferred`

## Donation recording transaction

1. Staff opens an eligible donor from the workflow queue.
2. Laravel authorizes the actor and center.
3. Staff chooses appointment or walk-in and records collection details.
4. Laravel rechecks eligibility at the moment of completion.
5. One database transaction:
   - Creates the donation/collection episode.
   - Links and completes the appointment when applicable.
   - Updates donor and donor-profile history.
   - Calculates the next eligible date.
   - Creates or assigns the unique donation identifier and original collection-container record in quarantine.
   - Links the expected specimen/label set without adding usable inventory.
   - Records donor-care outcomes and audit/outbox evidence.
   - Preserves the current legacy blood-unit compatibility record during migration until the approved component model replaces it.
6. Private after-visit notification delivery is queued after commit; the message contains no blood group, identifier, outcome, deferral, or other clinical detail.
7. Repeating the same request cannot create a second donation, collection identifier, original container, or duplicate specimen-label set. Derived components are created only through the later controlled processing workflow.

## Blood-group verification

Blood-group confidence moves through:

`unknown → user_selected → staff_verified`

1. Donor-provided blood group is informational.
2. Authorized staff records laboratory-confirmed ABO/Rh result.
3. Laravel stores verifier and verification time.
4. Donation, donor, and donor-profile fields remain consistent.
5. Changing an already verified blood group is high risk: it requires reason, elevated permission, confirmation, and audit before/after values.

## Donation identification, specimens, and barcode chain

1. After same-day eligibility and expiring identity confirmation, the construction workflow creates or receives one database-unique donation identifier from a locked center/year range.
2. Chair-side labels link the donor episode, original quarantined collection container, and every required specimen. Later test/component entities must extend this same identifier chain.
3. Print and scan-application record template, symbology, printer, count, operator, and time. Controlled replacement voids the old label with a reason and requires the new label to be printed and applied again.
4. Unmatched, duplicated, unreadable, voided, unapplied, replaced, or unaccounted labels block collection. Silent relabeling after collection starts is prohibited.
5. The current one-donation/one-legacy-blood-unit invariant is transitional. The target creates one collection record and may produce zero or more traceable components.
6. Adoption of ISBT 128 or a national equivalent requires approved national policy, product coding, label design, printer/scanner validation, and migration planning.

## Laboratory, quarantine, and authorized release

### Specimen and test workflow

1. Laboratory staff receive each specimen by barcode and confirm its donation/container match.
2. Rejected, missing, damaged, insufficient, or mismatched specimens are recorded and cannot silently disappear.
3. Approved rules create required blood-group, transfusion-transmissible-infection, and other test orders.
4. Each test run records method/instrument, reagent and control lots, operator, time, raw/result value, validity, repeats, discrepancies, and interface provenance.
5. Internal QC, EQA, analyzer maintenance, reagent expiry/recall, and staff competency are part of the routine workflow rather than separate paperwork.
6. Results are reviewed and authorized by permitted laboratory staff.

### Hard quarantine

Original containers and all components remain in physical and digital quarantine until every configured test, interpretation, processing, QC, identification, expiry, and cold-chain criterion is satisfied. Quarantined, incomplete, reactive, discrepant, invalid, failed-QC, recalled, expired, unlabelled, or unresolved-excursion components never contribute to available inventory.

### Release workflow

1. The release service evaluates the approved versioned criteria on the authoritative system.
2. It records tests and QC evaluated, criteria version, decision, approver, second approver where required, time, signature, reason, and exceptions.
3. The same technician cannot be the sole tester, verifier, and releaser where separation is required.
4. Failed criteria lead to repeat testing, investigation, rejection, discard, donor counselling/referral, recall/look-back, or another approved disposition.
5. Emergency release cannot transform untested or unsafe donor blood into routine released stock.

Representative laboratory/component states:

`identified → quarantined → specimen_received → testing → results_pending_review → processing/QC_pending → release_review → released | rejected | investigation_hold | recalled`

## Component production and lineage

1. Approved processing converts an original collection into zero or more products such as red-cell, plasma, platelet, or other nationally approved components.
2. Every component receives a unique product identifier while retaining the original donation identifier.
3. Parent-child lineage records splits, pools, modifications, processing method/device, operator, time, yields, QC, deviations, labels, storage requirements, and component-specific expiry.
4. Orphan products, unexplained yield differences, missing labels, or incomplete lineage remain held.
5. Product catalog, codes, shelf lives, additive solutions, quality criteria, and storage conditions are versioned national master data.

## Component inventory, storage, transfer, and distribution

### Component lifecycle

`quarantined → processing → QC_pending → release_review → released → available → reserved → allocated → issued → dispatched → received → transfused | returned | discarded | recalled`

Additional controlled states include `investigation_hold`, `expired`, `rejected`, `lost`, and `transfer_in_transit`. No user may jump directly from collection/testing to available.

### Inventory effects

- Inventory is component-level, not only center/blood-group totals.
- Authoritative state tracks product, ABO/Rh, special attributes, center, storage device/location, release, expiry, reservation, allocation, transfer, issue, dispatch, receipt, return, disposal, recall, and investigation hold.
- FEFO selects the earliest-expiring compatible component under approved rules; an exception requires reason and authority.
- Reservations and allocations prevent double promise and have controlled expiry/release.
- Every automatic or manual delta is auditable and cannot make stock negative.
- Reconciliation compares component records, aggregate balances, physical counts, transfers, issues, returns, disposal, and adjustments.

### Expiry, return, and disposal

1. Scheduled checks identify expiry risk and remove expired components from usable stock without deleting history.
2. Returned components require chain-of-custody, time, package, and temperature assessment before restocking.
3. Disposal records reason, method, actor, witness/approval where required, time, location, and evidence.
4. Wastage reasons distinguish collection failure, testing/release failure, processing loss, expiry, cold-chain excursion, damage, return failure, inappropriate request, and other approved causes.

### Center-to-center transfer

1. A transfer request records shortage/surplus reason, source, destination, components, urgency, requester, and approval.
2. Source staff reserve, pack, seal, add temperature monitoring, record custody, and dispatch.
3. Destination staff confirm count, label, seal, package, time, temperature evidence, discrepancies, and acceptance/hold/rejection.
4. Source and destination inventory update only through approved transfer states.

## Cold-chain and equipment workflow

1. Register storage/transport equipment, capacity, location, calibration, maintenance, alarm, backup power, responsible staff, and service state.
2. Capture continuous or approved interval temperature readings with device and synchronization provenance.
3. Alarm escalation records acknowledgement, response, backup storage, and overdue state.
4. An excursion automatically identifies and holds potentially affected components.
5. Quality investigation records duration/range, affected products, disposition, root cause, CAPA, and release/restock authority.

## Hospital request, compatibility, issue, and transfusion

### Electronic request

1. An authorized clinician submits patient reference, hospital/ward, indication, haemoglobin/relevant observations, bleeding state, urgency, component, quantity, and required time.
2. The system checks completeness and presents approved patient-blood-management guidance without replacing clinical judgment.
3. Outside-guidance requests require an override reason.
4. Request states expose review, shortage, partial fill, alternative, cancellation, and timestamps.

### Compatibility and emergency release

1. Patient and patient specimen are positively identified and linked to the request.
2. Hospital blood-bank/laboratory staff record ABO/Rh confirmation, antibody testing, compatibility/crossmatch, method, reagent/control context, validity, operator, and reviewer.
3. Incompatible, expired, recalled, unreceived, unapproved, wrong-patient, or wrong-request components are blocked.
4. Emergency release requires named clinical authorization, reason, acknowledgement of risk, selected component, and retrospective completion.

### Allocation, issue, dispatch, and receipt

1. Compatible FEFO components are allocated without double allocation.
2. Final issue confirms request, patient, component, release, compatibility/emergency authority, expiry, label, package, and staff.
3. Dispatch records vehicle/courier, route, package, logger, custody handoffs, departure, ETA, status, and proof of delivery.
4. Hospital receipt records receiving officer, time, package/seal, temperature evidence, count, discrepancies, and acceptance/hold.

### Bedside verification and outcome

1. Bedside verification confirms the right patient, component, request, time, expiry, compatibility/emergency authorization, and staff.
2. The transfusion record stores start, required observations, interruptions, completion, volume, outcome, and staff.
3. Every issued component ends as transfused, returned, discarded, recalled, lost, or another approved final disposition. Missing outcomes enter an overdue reconciliation queue.

## Haemovigilance, adverse events, recall, and look-back

### Donor and recipient events

- Record donor reactions and recipient transfusion events with severity, timing, treatment, referral, outcome, staff, equipment/supplies, and follow-up.
- Serious events escalate to the responsible hospital, center, NBTS quality/haemovigilance, and national authority according to policy.

### Recall and look-back

1. A case may start from later donor information, changed/reactive result, equipment/reagent concern, processing deviation, label error, cold-chain incident, or other approved trigger.
2. The platform locates every related donation, specimen, component, storage location, transfer, hospital, recipient, return, discard, and unaccounted item in both directions.
3. The case records containment, notifications, recovery/disposition, patient follow-up, deadlines, regulator communication, decision authority, and closure approval.
4. Closure is prohibited while critical components or recipients remain unexplained unless an authorized risk decision records the unresolved exception.

## Quality-management workflow

- Deviations and nonconformities connect containment, affected records, root cause, correction, corrective action, preventive action, owner, due date, effectiveness check, and closure.
- SOPs, questionnaires, test/release rules, labels, forms, and work instructions are versioned, approved, effective-dated, and linked to transactions.
- Staff training and task competency determine who may perform or approve work.
- EQA, internal audits, findings, CAPA, and hospital transfusion-committee reviews are tracked.
- Critical configuration and software changes follow formal clinical, quality, privacy, and technical change control.

## Offline, downtime, and reconciliation workflow

1. An authorized center registers an assigned device and receives a one-time credential. Server-reserved identifier batches are bounded, non-overlapping, expiring, and immediately revocable.
2. Laravel stores received payloads encrypted, fingerprinted, idempotent, and linked to device/operator/center/batch. The separately owned field client must provide approved protected local storage and wipe behavior.
3. Synchronization performs active-account, duplicate, deferral, interval, identity, screening, center, capacity, sequence, label, specimen, outcome, and quarantine validation.
4. Received, conflict, reconciled, and rejected states preserve the encrypted source and review evidence. A corrected conflict may be retried; an irreconcilable record is rejected with a reason, never deleted.
5. Laboratory, release, allocation, and transfusion controls remain authoritative; sync success creates quarantine only and never makes stock available.
6. Numbered no-store downtime forms preserve identifiers and later reconciliation. Production activation still requires the approved field dataset, device baseline, retention, loss, and remote-wipe procedure.

## Low-stock and emergency response

1. Every released-component inventory effect compares usable stock and days of supply with the approved center/region, component, blood-group, shelf-life, and demand threshold.
2. A shortage opens or updates one active low-stock alert with severity, owner, age, and escalation state.
3. Center managers, inventory/logistics coordinators, and permitted national staff are notified.
4. Staff first review reservations, transfers, component alternatives, expiry risk, transport time, laboratory/processing capacity, and hospital demand before creating a campaign.
5. Staff may create one linked emergency campaign; duplicate requests return the existing campaign.
6. Donor targeting selects only active, eligible, consented donors using approved blood-group, component need, location, timing, language, and communication rules.
7. Push, SMS, email, or other approved jobs are queued after commit, deduplicated, retried, and logged.
8. When stock and demand recover to the configured threshold, the alert resolves while retaining full history.

Alert states:

`open → notified → campaign_created → resolved`

## Loyalty workflow

1. Only a successfully completed donation changes loyalty totals.
2. The service recalculates total donations, volume, points, and tier.
3. Active badge and reward thresholds are evaluated idempotently.
4. Newly earned items are recorded once and trigger donor notification.
5. Reward redemption requires explicit state, actor, time, and audit.
6. Leaderboards use an approved period and privacy display rules.

## Notification workflow

1. Domain events request a notification after the originating transaction commits.
2. Laravel stores the in-app notification record first.
3. Channel selection respects donor preferences, consent, verified contact details, urgency, and system configuration.
4. FCM uses a service account and HTTP v1; invalid tokens are retired safely.
5. SMS reminders use configured providers and idempotency keys.
6. Email uses queued Laravel notifications/mail.
7. Every attempt records channel, status, provider identifier, retry information, and safe error details.

Primary triggers include appointment creation/confirmation/reminders/cancellation, eligibility changes, donation completion, badge/reward awards, low stock, emergency campaigns, and campaigns near eligible donors.

## Public content workflow

1. Authorized content staff create English and/or Kiswahili drafts.
2. Required fields, media, source attribution, and document uploads are validated.
3. A reviewer approves and publishes or schedules the item.
4. Only published, currently visible records appear publicly or in the donor API.
5. Archive/unpublish actions preserve history and are audited.

Managed content includes articles, publications, campaigns, centers, schedules, regional contacts, FAQs, leadership/governance information, public metrics, feedback categories, privacy, and terms.

### Managed-content language contract

- English (`en`) is the backward-compatible base locale because existing deployed text columns contain English or source-language content; Kiswahili (`sw`) is the second managed locale.
- Localized values use polymorphic translation records keyed by content type, record ID, field, and locale. Existing columns remain the English/base value, so legacy imports and API aliases do not break.
- The authoritative bilingual field map is `config/content.php`: article/publication title, category, summary, body, metadata and attachment label; campaign title, description and location; center name, address, opening hours, services, capacity label and type; badge/reward name and description; and static-page title, summary, body and metadata.
- Proper identifiers, donor/clinical records, blood groups, dates, phone/email values, URLs, coordinates, source names, and uploaded filenames are not translated. Controlled states use stable codes plus `operations.*` display labels.
- A requested non-base translation is used when present and non-blank; otherwise the English/base value is shown. The UI identifies fallback content to editors, but public readers never see an empty field.
- Publishing requires complete approved English/base fields. Missing Kiswahili is allowed during migration but is shown as an editorial warning; no machine translation is silently generated.
- Notifications are rendered in the recipient's preferred locale when sent and stored as the delivered snapshot. They are not retranslated after delivery.
- API resources keep stable field/state codes independent of locale and may add localized display text without replacing those codes.

## Public-page information architecture

- Home: mission, urgent actions, current campaigns, centers, trusted impact, and donor journey.
- About: mandate, mission, vision, governance, leadership, and national network.
- Donate: reasons, process, preparation, aftercare, whole blood, and apheresis.
- Services: recruitment/collection, laboratory testing, components, storage/supply, clinical guidance, and quality management.
- Eligibility: public guidance, deferrals, intervals, and staff-decision disclaimer.
- Centers: backend-driven search, filters, schedules, contacts, maps, and details.
- Campaigns: current/upcoming/emergency filters and details.
- News and publications: searchable approved records and accessible downloads.
- Impact: approved dated statistics and operational analytics safe for public release.
- Customer service: contact, feedback, complaints, charter, and response expectations.
- Download app: verified stores, package identity, QR link, supported platforms, and help.
- Legal: privacy, terms, cookies where applicable, and data-protection information.

Public facts and contact details must come from approved backend records or a verified NBTS source. Old or unclear statistics must show their period and cannot be presented as current.

Implemented public web contract:

- Named Laravel routes render the home, institutional information, donation guidance, services, eligibility, center directory/detail, campaign directory/detail, news directory/detail, publications, FAQ, contact, app-download guidance, and aggregate impact pages.
- Center, campaign, article, and publication visibility uses the same active/published lifecycle boundaries as the mobile API. Inactive centers, ended campaigns, drafts, archived records, and future publications are not exposed by detail routes.
- Center, campaign, and news searches are validated and retain their filters through pagination. Impact totals are calculated from aggregate operational records and do not expose donor identity or sensitive stock details.
- The public shell and static guidance are translated in English and Kiswahili. Existing managed records currently use their base stored value; Phase 4 implements the translation-record storage and editor defined by the managed-content language contract above.
- App-store links and QR destinations are withheld until approved URLs exist. The app preview uses an anonymized donor label and no Firebase credentials, personal data, or service-account material is placed in public assets.

## Staff account navigation flow

Navigation follows work, center context, assignment, competency, and permission—not table names:

1. **Overview** — national/center/department/hospital queues, incidents, SLA, and shortcuts.
2. **Donor reception** — scan/search, duplicates, register, identity, profile, donor card, appointment/check-in.
3. **Screening & counselling** — questionnaire, eligibility, deferrals, referral, re-entry, history.
4. **Collection** — donor confirmation, donation identifier, labels, collection, specimens, donor reactions, handoff.
5. **Laboratory & release** — specimen receipt, test orders/runs, QC, repeats, discrepancies, review, quarantine, and authorized release.
6. **Components** — processing, parent-child lineage, labels, QC, production deviations, and yields.
7. **Inventory & storage** — component stock, FEFO, reservations, locations/devices, expiry, returns, disposal, and reconciliation.
8. **Logistics** — transfer, pack-out, cold chain, dispatch, custody, delivery, and receipt exceptions.
9. **Hospitals & transfusion** — requests, compatibility, emergency release, allocation, issue, receipt, bedside verification, outcomes, and returns.
10. **Quality & haemovigilance** — donor/recipient events, recall, look-back, deviations, CAPA, SOPs, competency, EQA, and audits.
11. **Response & engagement** — low-stock alerts, campaigns, targeted communication, notifications, loyalty, and feedback.
12. **Content** — news, publications, FAQs, schedules, regional contacts, legal pages, and approved public metrics.
13. **Intelligence** — operational, clinical, laboratory, inventory, hospital, quality, safety, security, cost, PDF, and export reports.
14. **Administration** — hierarchy, centers, departments, users, assignments, permissions, master data, configuration, integrations, audit, recovery, support, and change control.
15. **Account** — profile, language, appearance, password, 2FA, passkeys, sessions/devices, and security history.

Dashboard numbers always link to the queue or evidence that explains them. High-risk actions require reason, confirmation, current competency, and independent approval where configured.

The implemented staff command-center navigation presents only destinations permitted for the signed-in user and groups them in workflow order. On desktop, staff can collapse the 244-pixel navigation to a persistent 60-pixel icon rail; every destination remains available, exposes its translated label as a tooltip, and the active location remains visually distinct. The reduced-motion preference disables the width and item-motion transitions. Mobile continues to use the sidebar toggle rather than the saved desktop state.

## Audit workflow

Sensitive actions record:

- Stable action code.
- Actor and impersonator when applicable.
- Subject type and identifier.
- Blood-center context.
- Request/correlation identifier.
- Safe before and after values.
- Reason for high-risk changes.
- IP address and user agent.
- Timestamp.

Ordinary application roles cannot update or delete audit records. Secret values, password material, passkey credentials, access tokens, Firebase tokens, and service-account data are redacted.

## Disaster-recovery workflow

1. Scheduled jobs create encrypted database and required media/document backups.
2. Backups are copied to an approved off-site store under retention policy.
3. Verification jobs confirm existence, size, age, checksum/readability, and alert on failure.
4. A restore never targets the live database during testing.
5. Authorized operators restore into an isolated environment, run migrations/checks, compare critical counts, and execute smoke tests.
6. The result, operator, timing, recovery point, and exceptions are recorded.
7. Production recovery follows an approved runbook with communication, write freeze, restore, verification, controlled reopening, and post-incident review.

## PDF and export workflow

1. The user requests a document from an authorized record or report.
2. Laravel authorizes record and center scope.
3. The service builds a versioned data snapshot so the document is reproducible.
4. The selected locale controls labels while identifiers and codes remain stable.
5. Laravel renders a branded accessible PDF with issue time, source period, document identifier, and verification context where appropriate.
6. Generation/download is audited for sensitive documents.
7. Large reports run through queues and notify the requester when ready.

## Mobile API compatibility contract

The target API prefix is `/api/v1`. Required capability groups:

- Authentication: register, login, Firebase login, logout.
- User/profile: current user, profile, profile update, profile photo.
- Discovery: campaigns, articles, publications, centers, available slots.
- Donor: donor card, eligibility, loyalty, leaderboard, donation history and summary.
- Appointments: list, upcoming, create, reschedule/update, cancel.
- Notifications: list, unread count, register token, mark all read, read one, delete one.
- Staff: donor search/reception, screening/deferrals, collection/identifiers, specimen/laboratory/QC, quarantine/release, component processing, inventory/cold chain, transfers/logistics, hospital request/compatibility/issue, transfusion outcomes, haemovigilance/recall/CAPA, alerts, campaigns, and reports. New safety-critical endpoints require their own approved versioned contracts rather than silent expansion of donor API v1.

Implemented authentication contract:

- `POST /api/v1/auth/register`: accepts the existing Flutter registration payload and optional `device_name`; omitted device names default to `NBTS Mobile` for compatibility.
- `POST /api/v1/auth/login`: accepts `identifier`, `password`, and optional `device_name`; legacy `email` or `phone` identifier keys are normalized.
- `POST /api/v1/auth/firebase`: `{ firebase_id_token, device_name }` → `{ token_type, token, expires_at, user }`.
- `GET /api/v1/me`: requires a Sanctum bearer token with `donor:read` and returns `{ data: user }`.
- `POST /api/v1/logout`: revokes only the presented bearer token and returns HTTP 204.
- `X-Locale: en|sw` (or `Accept-Language`) controls localized API errors; persisted domain states remain stable codes.

Implemented profile contract:

- `GET /api/v1/profile` and compatibility aliases `GET /api/v1/me` and `GET /api/v1/user` return the current donor.
- `PUT /api/v1/profile` updates only the current donor's approved profile/preference fields and requires `donor:write`.
- `POST /api/v1/profile/photo` accepts a validated raster image up to 5 MB and 3000×3000 pixels, stores it on the public disk, and safely replaces prior local files.
- Donors cannot change a staff-verified blood group; preferred centers must be active; phone numbers are database-unique.
- Compatibility fields used by the existing Flutter `User` model are available at the top level while richer donor details remain nested under `donor_profile`.

Implemented center and appointment contract:

- `GET /api/v1/blood-centers` and `GET /api/v1/blood-centers/{id}` expose active centers with Flutter aliases, search/city/service filters, and bounded pagination.
- `GET /api/v1/blood-centers/{id}/available-slots?date=YYYY-MM-DD` exposes stable slot aliases, availability, reason code, and localized reason.
- `GET /api/v1/appointments`, `/appointments/upcoming`, and `/appointments/{id}` require `donor:read` and return only the authenticated donor's records.
- `POST /api/v1/appointments`, `PUT /api/v1/appointments/{id}`, and `POST /api/v1/appointments/{id}/cancel` require `donor:write`.
- Booking accepts both `blood_center_id` and the legacy `center_id`; resources expose both identifiers and the center name.

Implemented donor card, eligibility, and history contract:

- `GET /api/v1/donor-card` returns the authenticated donor's card, current donor-facing statistics, and an `nbtsqr` payload signed with HMAC-SHA256. The payload expires after five minutes by default and can use a dedicated environment signing key.
- QR payload verification rejects malformed, modified, expired, inactive-account, non-donor, and identity-mismatched cards. Card refreshes are intentionally not audited to avoid a high-volume low-value audit trail; future staff scans must audit the lookup/use event.
- `GET /api/v1/eligibility` prioritizes active deferrals, persisted staff decisions, and the next-donation interval. It returns stable eligibility codes and localized donor guidance while always stating that clinical screening is required at the center.
- The read-only mobile eligibility summary never creates a screening record, lifts a deferral, or authorizes donation completion; those remain staff-controlled workflow actions.
- `GET /api/v1/donations` returns only the authenticated donor's history using bounded pagination and Flutter-compatible date, center, blood-group, volume, status, and type aliases.
- `GET /api/v1/donations/summary` calculates totals from completed donation records rather than trusting cached profile counters. The legacy `lives_touched` value remains an explicitly flagged estimate.
- All four endpoints require an active Sanctum token with `donor:read`; donor role and record ownership are enforced server-side.

Implemented loyalty and leaderboard contract:

- `GET /api/v1/loyalty` returns the authenticated donor's points, tier, completed-donation count, all-time rank, badges, and rewards. Compatibility aliases expose both `points`/`loyalty_points` and `tier`/`loyalty_tier`.
- `GET /api/v1/leaderboard?period=all_time&per_page=20` is bounded to 50 entries and exposes only donors who enabled anonymized sharing.
- Leaderboard entries never expose a donor's real name or contact details. Stable display labels use rank-based values such as `Donor 001`, and `is_current_user` identifies the authenticated donor without de-anonymizing other entries.
- Both endpoints require an active donor token with `donor:read`; donor role and ownership/privacy policies are enforced server-side.

Implemented public/mobile discovery contract:

- `GET /api/v1/campaigns` and `/campaigns/{id}` expose only `upcoming` or `ongoing` campaigns whose end time has not passed and whose owning blood center is active.
- Campaign filters include text, status, type, target blood group, and center. Emergency campaigns sort first, followed by event start time.
- `GET /api/v1/articles` and `/articles/{id}` expose only `published` articles whose non-null publication time has arrived. Draft, archived, and future-scheduled records return no public data.
- Article filters include text, category, featured state, and bounded pagination. The response retains the body because the canonical Flutter dashboard opens article details from its list payload.
- `GET /api/v1/publications` and `/publications/{id}` reuse published article records that have an approved attachment, preserving the deployed schema while exposing document metadata and download URLs.
- `GET /api/v1/schedules` and `/schedules/{id}` expose the schedule/location projection of publicly visible campaign and center records. This avoids inventing a second scheduling table before staff content requirements are finalized.
- All discovery endpoints are public, validated, bounded to 50 records per page, and return storage-disk URLs or approved external media URLs.

Implemented notification and device-token contract:

- `GET /api/v1/notifications` returns only the authenticated donor's inbox with bounded pagination, optional unread/type filters, Flutter aliases, and an authoritative unread count in response metadata.
- `GET /api/v1/notifications/unread-count` supports the dashboard badge without downloading the inbox.
- `POST /api/v1/notifications/{id}/read`, `POST /api/v1/notifications/mark-all-read`, and `DELETE /api/v1/notifications/{id}` require `donor:write` and return 404 for records owned by another donor.
- `POST /api/v1/notifications/register-token` validates Android/iOS FCM tokens, deduplicates globally, and safely reassigns a refreshed device token to the currently authenticated donor.
- `DELETE /api/v1/notifications/device-token` unregisters only a token owned by the authenticated donor and is idempotent for missing or foreign tokens.
- Device registration/removal is audited with the platform and SHA-256 token fingerprint. Raw FCM tokens never enter audit metadata.
- Registering a token does not override notification consent; queued delivery must still check `push_notifications_enabled` and retire provider-invalid tokens.
- The optional Firebase push transport sends HTTP v1 multicast messages, retires invalid and unknown tokens only for the intended recipient, records the provider message identifier, and lets the existing retry tracker handle provider failures. A real service-account key and test device are still required for production delivery proof.

Compatibility fields currently required by Flutter include:

- User: `id`, `name`, `email`, `phone`, `blood_group`, `gender`, `region`, `date_of_birth`, `donor_id`, `preferred_center`, `loyalty_tier`, `loyalty_points`, `total_donations`, authoritative completed-donation `total_volume_ml`, `next_eligible_date`, channel-consent fields, and `share_anonymized_data`.
- Campaign: `id`, `title`, `summary`, `description`, `category`, `type`, `blood_group`, `blood_type`, `starts_at`, `start_date`, `ends_at`, `end_date`, `urgent`.
- Center: `id`, `name`, `address`, `phone`, `phone_number`, `opening_hours`, `hours`, `wait_time`, `capacity_label`, `services`, `is_open`.
- Appointment: `id`, `scheduled_at`, `blood_center_id`, `center_id`, `center_name`, `status`, `notes`.
- Donation: `id`, `donation_date`, `donated_at`, `blood_group`, `blood_type`, `volume_ml`, `status`, `donation_type`.
- Donor card: `donor_id`, `qr_payload`, `qr_expires_at`, `donor`, `stats`.
- Notification: `id`, `title`, `body`, `message`, `type`, `read`, `read_at`, `sent_at`, `created_at`.

The canonical Flutter repository is `NBTS/nbts-mobile`. Its API client sends bearer tokens and the current locale, unregisters the device token before logout, and includes models/repositories for loyalty, leaderboard, publications, and donation schedules. Dashboard discovery and recognition surfaces consume those repositories. Flutter 3.44/Dart 3.12 analysis and the 8-test API/model/repository plus welcome/registration suite pass in an isolated SDK container. Authentication-provider checks and Android device execution remain acceptance gates; iOS is unsupported until its bundle and Firebase configuration are approved.

Further Flutter implementation and device acceptance belong to the separate mobile owner. Laravel changes must publish exact requests, responses, aliases, security boundaries, and verification commands in `docs/technical/api.md`; requested mobile workarounds must be reviewed as versioned API-contract decisions rather than silently changing server behavior.

Aliases can remain during transition but should be normalized in a future versioned API, never silently removed from v1.

## Reports and operational intelligence

Reports are center/facility/department scoped and filterable by authorized period, donor group, component, blood group, request, campaign, status, event, and other approved dimensions. The KPI dictionary defines numerator, denominator, exclusions, source, owner, frequency, target, and data-quality checks.

Required outputs include:

- Donor awareness, registration, appointment, waiting, screening, deferral, re-entry, repeat donation, reactions, and satisfaction.
- Collection attempts, completed collections, usable yield, incomplete/failed collections, blood group, center, method, and donor type.
- Specimen rejection, test turnaround, invalid/repeat runs, reactive/discrepant results, QC, EQA, reagent stock, analyzer/interface downtime, and release time.
- Component production, lineage completeness, yield, processing deviation, quarantine, release, rejection, and component-specific expiry.
- Inventory by component/group/location/state, FEFO exceptions, reservations, allocations, stock days, transfer, return, expiry, discard reason, and reconciliation exception.
- Hospital request completeness, appropriateness indicators, fill/partial-fill/unmet demand, compatibility time, emergency release, issue, delivery, receipt, transfusion outcome, return, and wastage.
- Cold-chain readings, alarms, acknowledgement, excursion, affected components, disposition, and CAPA.
- Donor/recipient adverse events, investigation, recurrence, recall/look-back completion, unresolved components/recipients, CAPA, SOP, competency, audit, and EQA.
- Notification delivery, campaign response, content/customer service, privacy, access review, security, incident/SLA, backup, restore, downtime, and change success.
- Cost and sustainability indicators where approved, without allowing cost pressure to override safety rules.

Balanced scorecards must prevent collection totals from hiding discard, expiry, inappropriate use, adverse events, or failed traceability. Every metric shows definition, source period, data-quality status, and a path to supporting records.

## Core invariants

### Identity and donor safety

- A person has one approved donor master identity and stable donor ID; duplicate resolution cannot erase provenance.
- Deferred, inactive, duplicate, not-yet-eligible, or unresolved donors cannot be collected through another channel or offline copy.
- Donor-provided blood group is never represented as laboratory verified.
- Sensitive deferral/test information is not disclosed through unsafe messages or unauthorized roles.

### Collection, specimen, and laboratory safety

- Every collection has one unique donation identifier and every bag, specimen, test, and component remains linked to it.
- A collection may create zero or more components; component lineage must reconcile to the parent collection.
- An unmatched/mislabeled specimen or component cannot proceed as normal.
- Required tests, QC, interpretation, and approvals are complete before release.
- Quarantined, reactive, discrepant, invalid, failed-QC, expired, recalled, unlabelled, or excursion-affected stock is never available.
- The person performing a test cannot be the sole verifier/releaser where independent control is required.

### Inventory, logistics, and clinical-use safety

- A component transition affects authoritative inventory at most once and inventory cannot be negative.
- A component cannot be simultaneously available to multiple allocations or locations.
- A transfer or dispatch remains in transit until the destination records receipt/discrepancy.
- Incompatible, expired, recalled, unreleased, wrong-patient, wrong-request, or unissued components cannot be transfused.
- Every issued component reaches a final accounted disposition.
- Donor-to-recipient and recipient-to-donor traceability remains available for the approved retention period.

### Access, evidence, resilience, and change

- Center/hospital-scoped users cannot read or mutate records outside assignments and minimum necessary purpose.
- Inactive users, expired assignments, missing competency, or conflicting duties remove the affected authority.
- Remote effects run only after the originating transaction commits and use idempotency/reconciliation.
- Audit evidence never stores passwords, private tokens, service credentials, or unnecessary health data.
- Public content is not visible before approval/publication and public metrics are aggregated and dated.
- Offline/downtime operation preserves identifiers, custody, quarantine, and later reconciliation.
- Backup/restore, change control, and rule versioning preserve the ability to explain which logic governed every safety-critical decision.
- API v1 fields are not silently removed without coordinated versioned migration.

## Implementation and rollout sequence

1. **Foundation and discovery:** current-state mapping, legal/policy review, center/facility/equipment inventory, baseline KPIs, data dictionary, risk register, safety case, target process, identifiers, component/test/release/hospital/offline/DR decisions. Indicative 6–10 weeks.
2. **Core safety pilot:** unique IDs/barcodes, collection/specimens, laboratory/QC, quarantine/release, component lineage, component inventory, one hospital request/compatibility/issue/dispatch/receipt/transfusion chain, traceability and recall drills. Indicative 4–6 months after approval.
3. **Controlled regional scale:** offline/mobile collection, additional hospitals, transfers, cold-chain telemetry, haemovigilance, interfaces, support, DR, data-quality and adoption gates. Indicative 4–8 months.
4. **National optimization:** national balancing, forecasting, de-identified analytics, more integrations, donor segmentation, continuous quality improvement, sustainable budget, and vendor-exit proof. Indicative 6–12 months.

These are planning ranges, not contractual estimates. Each stage exits on safety, quality, traceability, recovery, training, adoption, and approval evidence—not elapsed time alone.

## Known legacy corrections carried forward

- Use `blood_centers.phone`; `phone_number` is a temporary API compatibility alias.
- Campaign states remain `upcoming`, `ongoing`, `completed`, and `cancelled`; `is_active` is derived rather than a separate state.
- Firebase token verification requires the configured project ID.
- FCM HTTP v1 uses a service-account credential; the legacy server key is not required.
- The old test suite did not prove the core business rules; all migrated behavior requires new Pest coverage.
- The old donor interval used a single 90-day rule. The target uses approved configurable policy, including gender-specific intervals when confirmed.
- Legacy documentation sometimes says Filament is the account boundary. The target staff interface is Livewire/Flux unless a later explicit decision retains Filament for a bounded administrative module.

## Completion evidence

Implementation status is tracked in `docs/planning/task.md`. Proven completed work is recorded in `docs/evidence/achievement.md`. A workflow is complete only when its domain tests, authorization tests, UI/API tests, operational evidence, and relevant browser/device checks pass.
