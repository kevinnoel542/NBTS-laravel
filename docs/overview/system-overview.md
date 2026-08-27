# NBTS system overview

Last updated: 2026-08-12  
Document ID: `SYS-OVERVIEW-001`  
Owner: Product owner / Technical lead  
Status: Review  
Approval: Product direction approved for implementation on 2026-08-12; clinical, laboratory, quality, hospital, privacy, Ministry, and operational approval remains required for the affected safety-critical rules.  
Related requirements: `GOV-STRUCT`, `GOV-DEPT`, `GOV-ROLE`, `GOV-SOD`, `GOV-VIS`, `GOV-OVERVIEW`  
Related ADRs: Required ADRs listed in `documentation-register.md` remain pending.  
Related policies/SOPs: Approved NBTS and Ministry policies must be linked before production acceptance.

## 1. Purpose

NBTS-NEW is the Laravel-based operational and public-service platform for the National Blood Transfusion Service. It currently supports a verified donor-engagement and basic blood-center workflow and is intended to grow into a controlled national blood-management service.

This document defines the product boundary, users, modules, information flow, operational outcomes, and implementation constraints. It separates the verified current foundation from the target operating model. Target text is not evidence that a feature exists or that a clinical rule is approved.

## 2. Service outcomes

The platform is intended to help NBTS:

- Give donors trustworthy public information and a consistent mobile journey.
- Give staff a role-aware command center focused on work that needs action now.
- Preserve donor, collection, blood-unit, inventory, and future component traceability.
- Prevent cross-center, cross-hospital, and cross-department access outside active assignments.
- Separate technical administration from clinical authority.
- Make shortages, delays, exceptions, incidents, and overdue work visible with accountable owners.
- Produce evidence-linked reports rather than decorative totals.
- Maintain English and Kiswahili access across supported public, staff, and mobile experiences.
- Support controlled pilot validation, regional expansion, recovery, and sustainable national operation.

## 3. Verified current foundation

The current Laravel implementation includes:

- Fortify staff authentication, password confirmation, two-factor authentication, passkeys, sessions, account status enforcement, and English/Kiswahili preferences.
- Five compatibility roles remain available: super administrator, NBTS administrator, center manager, center staff, and donor.
- Twenty-six target role profiles plus two compatibility-only transition codes seed idempotently with explicit Spatie permission mappings.
- Additive organization units, departments, work locations, competencies, effective-dated staff assignments, and an ownership-checked active-assignment context.
- A non-destructive, idempotent backfill from deployed `center_staff` records, with compatibility fallback retained during migration.
- Donor registration, donor profile, stable donor ID, donor search, digital donor card, and signed QR payloads.
- Appointment booking and staff appointment progression.
- Staff-led eligibility screening, deferrals, deferral lifting, and donation-interval enforcement.
- Transactional donation recording, blood-group verification, compatibility blood-unit creation, inventory updates, expiry, disposal, manual adjustment, and reconciliation.
- Low-stock alerts, emergency campaigns, donor targeting, loyalty, rewards, leaderboards, and multi-channel notification orchestration.
- Public website pages and versioned `/api/v1` donor contracts.
- A Livewire/Flux staff command center with a collapsible navigation rail, center context, compact metrics, priority queues, filters, configurable tables, pagination, and audited actions.

Verified implementation evidence belongs in `achievement.md`. This overview does not replace that evidence.

## 4. Target national extension

The target service adds controlled foundations and workflows for:

- National, regional/zonal, blood-center, department, hospital, and work-location organization.
- Twenty-six operational role profiles and effective-dated scoped assignments.
- Competency-aware authorization and separation of duties.
- Collection identifiers, specimens, labels, and approved barcode standards.
- Laboratory test orders, runs, QC, repeats, discrepancies, quarantine, verification, and release.
- Component production, parent-child lineage, yields, labeling, and component-level inventory.
- Storage locations, FEFO, reservations, transfers, transport, dispatch, receipt, cold-chain devices, and excursions.
- Hospital requests, patient/specimen references, compatibility, emergency release, allocation, issue, bedside verification, transfusion outcomes, returns, and final disposition.
- Haemovigilance, adverse events, recall, look-back, investigation, deviation, root cause, and CAPA.
- Quality management, document control, competency, interoperability, offline/downtime operation, disaster recovery, security, support, and controlled rollout.

These target modules remain governed by `task.md`, `workflow.md`, the documentation register, required ADRs, and formal domain approvals.

## 5. Users and channels

### 5.1 Public visitor

Uses the public website without an account to read approved information, find centers and campaigns, review eligibility guidance, access customer service, and find verified mobile-app links.

### 5.2 Donor

Uses the Flutter application and donor API to manage only their own donor journey and approved public information. Donors cannot access the staff command center.

### 5.3 Staff and operational users

Use the Laravel Livewire/Flux command center. Access is calculated from account status, active assignment, role profile, organization/center/hospital scope, department, competency where required, record context, and separation-of-duty rules.

### 5.4 External systems and devices

Approved analyzers, LIS/HMIS systems, scanners, printers, temperature devices, messaging providers, and field devices integrate through validated interfaces. No integration writes directly to authoritative domain tables.

## 6. System boundaries

### 6.1 Laravel web application

Laravel owns staff authentication, authorization, operational transactions, audit evidence, dashboard composition, queues, scheduling, reporting, PDF generation, public content management, and API contracts.

### 6.2 Donor API and Flutter application

Laravel owns the mobile API contract and server-side authorization. Flutter owns donor presentation and device behavior. Laravel changes must keep `api.md` synchronized and must not require uncoordinated mobile repository changes.

### 6.3 Data store

MySQL stores authoritative operational records. Schema changes are additive, deployed migrations are immutable, and migrations must preserve the existing cloned database. Safety-critical state changes remain transactional and auditable.

### 6.4 Notifications and external providers

In-app notifications are native. Email, SMS, and push use tracked adapters with retries and safe failure details. Credentials stay outside Git and controlled documentation.

### 6.5 Out of scope for Phase 5

Phase 5 does not implement laboratory release, component production, hospital compatibility, transfusion authority, or other later clinical workflows. It creates the organization, assignment, permission, dashboard, and evidence foundation those modules will use.

## 7. Organization and authorization model

The target hierarchy is:

`National NBTS → approved zone/region → blood center or hospital interface → department → work/storage location`

Authorization follows these principles:

- One person has one identity account.
- A staff member may hold multiple effective-dated assignments.
- Each assignment links one role profile to one organization scope and optional department/work location.
- The user selects an active assignment when more than one is available.
- Navigation, dashboard configuration, queue visibility, actions, and data scope are recalculated from the active assignment.
- Global role membership alone cannot grant authority at every assigned center.
- Assignment deactivation, expiry, organization suspension, or account deactivation removes current access immediately while retaining history.
- Super-administrator access does not automatically grant clinical release or transfusion authority.

Detailed rules are defined in `center-operating-model.md` and `roles-and-permissions.md`.

## 8. Role-aware command center

The staff dashboard is one shared Livewire shell composed into thirteen staff configurations. It is not twenty-six duplicated pages. A separate donor home remains in Flutter.

Every staff dashboard provides concise operational information in this order:

1. Active role, organization/center/hospital, department, and responsibility summary.
2. Critical work, work due today, work in progress, overdue work, and completed work where meaningful.
3. Priority queue with severity, age, owner, SLA/escalation state, and direct action.
4. Role-specific operational pulse, such as donor flow, stock, staffing, safety, delivery, or system health.
5. Exceptions and risks that need attention.
6. Recent accountable activity and role-appropriate quick actions.

Dashboard rules:

- Every number links to the records that explain it.
- Metrics are defined, scoped, and sourced; invented values are prohibited.
- Urgent, unsafe, blocked, and overdue work appears before routine totals.
- Panels unavailable to the active role or current implementation are removed and the layout reflows without empty gaps.
- Compact connected metrics and content-sized panels replace oversized cards.
- High-risk actions remain in their controlled workflow rather than executing from an unguarded dashboard shortcut.
- Expensive secondary regions may load independently; the primary actionable queue loads first.
- English/Kiswahili, light/dark appearance, keyboard navigation, focus states, loading, empty, error, and reduced-motion behavior are consistent.

The target configurations are documented in `workflow.md` and `roles-and-permissions.md`.

## 9. Module map

| Module | Primary purpose | Current state |
| --- | --- | --- |
| Identity and account security | Staff/donor authentication, account status, password, 2FA, passkeys, sessions | Verified foundation |
| Organization and assignments | Hierarchy, departments, locations, roles, effective assignments, active context | Verified Phase 5 foundation; external hierarchy/capability approval pending |
| Donor reception | Search, duplicate review, registration, donor card, arrival | Verified basic foundation; national extension continues |
| Appointments | Slot discovery, booking, rescheduling, check-in, cancellation, completion | Verified foundation |
| Screening and deferrals | Eligibility decision, donor safety, deferral and re-entry | Verified basic foundation |
| Collection and identification | Collection, identifier, specimens, labels, reactions, handoff | Partial compatibility foundation; Phase 6 target |
| Laboratory and release | Testing, QC, quarantine, independent verification and release | Phase 7 target |
| Components and inventory | Component lineage, stock, FEFO, reservations, disposal | Basic unit inventory verified; Phase 8 extension |
| Logistics and cold chain | Transfer, dispatch, custody, receipt, sensors and excursions | Phase 8 target |
| Hospital and transfusion | Request, compatibility, issue, receipt, bedside verification and outcomes | Phase 9 target |
| Quality and haemovigilance | Events, investigation, recall, CAPA and quality records | Phase 10 target |
| Engagement and content | Campaigns, notifications, loyalty, news and publications | Verified basic foundation; management extension pending |
| Intelligence and reporting | Operational evidence, KPIs, exports and PDFs | Basic foundation; Phase 12 extension |
| Operations and resilience | Monitoring, backups, restore, support, downtime and recovery | Partial foundation; Phase 11 target |

## 10. Data, audit, and privacy

- Access follows least privilege and minimum necessary purpose.
- Donor and future patient-linked records remain scoped and are not exposed as dashboard detail without authority.
- Sensitive actions record stable action code, actor, impersonator where applicable, scope, subject, safe before/after values, reason, request identifier, IP/user agent, and timestamp.
- Assignment and permission changes are auditable and are not physically deleted to hide history.
- Secrets, Firebase credentials, recovery codes, tokens, and unnecessary personal/health data are excluded from documentation and audit metadata.
- Reports and dashboards use de-identified or aggregate data unless identified access is specifically authorized.

## 11. Quality attributes

### Security

Inactive accounts and assignments cannot retain permission. Cross-center and cross-hospital isolation must be proven by positive and negative authorization tests.

### Safety

Future release, compatibility, transfusion, emergency, recall, and cold-chain authority requires approved rules, explicit state transitions, independent approval where required, and bypass-prevention tests.

### Performance

Dashboard queries use scoped aggregates, explicit indexes, selected columns, and eager loading. Blade templates do not query the database. Expensive independent regions may be deferred without delaying the primary queue.

### Accessibility

The staff shell supports semantic headings and landmarks, keyboard operation, visible focus, sufficient contrast, reduced motion, responsive reflow, and meaningful loading/empty/error states.

### Localization

English and Kiswahili labels, summaries, actions, validation, empty states, and accessibility text are required for implemented staff features. Stable database codes remain untranslated.

### Recoverability

Schema and assignment migration is additive. Existing compatibility records remain available until backfill, reconciliation, rollback, and authorization equivalence are verified.

## 12. Implementation constraints

- Laravel 13, Livewire 4, Flux UI Free 2, Tailwind CSS 4, Fortify, Sanctum, Spatie Permission, Pest, and the existing project conventions remain the approved stack.
- Flutter code is outside the Laravel workstream unless explicitly requested.
- Existing deployed migrations are immutable.
- No dependency change is allowed without approval.
- No feature is complete without required automated tests, browser/device evidence, documentation synchronization, and an evidence-only achievement entry.
- Later clinical modules cannot be called complete from role profiles or dashboard placeholders alone.

## 13. Success and completion evidence

Phase 5 succeeds when:

- Organization, department, role-profile, assignment, and active-context records are additive and reconciled with existing users/centers.
- Twenty-six target profiles and their scoped permissions are seeded idempotently.
- Multiple assignments do not leak authority between centers, departments, or hospitals.
- Assignment removal removes access immediately without deleting historical accountability.
- Each implemented role receives the correct dashboard configuration and cannot open forbidden queues.
- Five compatibility demo accounts are available locally with documented local-only credentials.
- Focused and regression tests pass.
- Visible 1600×900 browser QA passes without JavaScript errors, horizontal overflow, inaccessible controls, oversized empty cards, or unsupported claims.
- `task.md`, `workflow.md`, the controlled module documents, and `achievement.md` are synchronized with verified reality.

## 14. Approval and unresolved decisions

Product direction and implementation are approved by the project owner on 2026-08-12.

The following remain external approval requirements before affected production use:

- Final national and zonal/regional hierarchy.
- Final center-type and capability catalogue.
- Final department ownership and escalation model.
- Clinical role competency requirements.
- Separation-of-duty combinations for test, verification, release, emergency issue, compatibility, and transfusion.
- Hospital organization and patient-data boundary.
- National identifier/barcode, laboratory, component, cold-chain, retention, downtime, and recovery policies.

Until approval, implementation must default to no clinical authority and must not fabricate later-phase operational data.
