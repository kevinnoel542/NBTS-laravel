# NBTS roles and permissions

Last updated: 2026-08-12  
Document ID: `ROLE-MATRIX-001`  
Owner: Product owner / Security owner  
Status: Review  
Approval: Product role and dashboard direction approved on 2026-08-12; NBTS operations, clinical, laboratory, quality, hospital, privacy, and Ministry approval remains required for production authority.  
Related requirements: `GOV-ROLE`, `GOV-SOD`, `GOV-VIS`, `GOV-OVERVIEW`  
Related ADRs: `ADR-004-quarantine-release-authority.md` and `ADR-005-hospital-integration-boundary.md` remain pending.  
Related policies/SOPs: Approved role, competency, laboratory release, emergency issue, hospital, privacy, audit, and retention policies must be linked before production acceptance.

## 1. Purpose

This document defines the target twenty-six role profiles, their scope, dashboard configuration, permission intent, assignment rules, and separation-of-duty boundaries.

It is the target authorization contract for Phase 5. Later clinical permissions are intentionally defined as target authority only and do not prove the corresponding laboratory, component, hospital, transfusion, haemovigilance, or recall workflow exists.

## 2. Authorization model

Effective permission is the intersection of:

`active account × effective assignment × active organization × role permission × department/location scope × approved capability × competency × record context × separation-of-duty rule`

No single factor grants authority by itself.

Rules:

- One person has one account.
- A person may hold several assignments, but each permission stays inside its assignment scope.
- The active assignment controls navigation, dashboard, queues, and actions.
- Spatie role permission alone is insufficient for scoped operational authority.
- Donors have no staff assignment and remain ownership-scoped through the mobile API.
- Super administrator and ICT roles do not automatically receive clinical release, compatibility, emergency issue, or transfusion authority.
- Role assignment never replaces record-level actor/reviewer separation.
- Inactive, suspended, expired, or revoked accounts/assignments have no current authority.

## 3. Compatibility boundary

The current implementation has five compatibility roles:

- `super_admin`
- `nbts_admin`
- `center_manager`
- `center_staff`
- `donor`

The target catalogue reuses `super_admin`, `center_manager`, and `donor`. The broad `nbts_admin` and `center_staff` roles remain temporary migration roles, so the Spatie role table contains twenty-eight role codes during transition: twenty-six target profiles plus those two compatibility-only roles.

Compatibility roles remain available until existing accounts, policies, seeders, and browser flows are migrated and reconciled. They must not silently acquire future clinical permissions.

## 4. Permission-level legend

The matrices use these levels:

| Code | Meaning |
| --- | --- |
| `—` | No access |
| `V` | View permitted records and evidence |
| `O` | Perform routine operational actions in scope |
| `M` | Manage, coordinate, configure, or assign within scope |
| `A` | Perform an explicitly approved high-risk decision, still subject to competency, state, reason, audit, and independent control |

Levels are domain-specific. `M` or `A` in one domain grants nothing in another domain. Approval authority does not permit the approver to bypass required inputs or approve their own earlier action where separation is required.

## 5. Target role catalogue

| No. | Stable code | Display name | Scope | Dashboard |
| ---: | --- | --- | --- | --- |
| 1 | `super_admin` | Super administrator | Platform | System control |
| 2 | `ict_security_operator` | ICT/security operator | Platform | System control |
| 3 | `national_operations_administrator` | National operations administrator | National | National operations |
| 4 | `national_quality_haemovigilance_officer` | National quality/haemovigilance officer | National | National quality and governance |
| 5 | `national_inventory_logistics_coordinator` | National inventory/logistics coordinator | National | National inventory and logistics |
| 6 | `national_donor_engagement_content_officer` | National donor engagement/content officer | National | Engagement and content |
| 7 | `data_protection_governance_officer` | Data-protection/governance officer | National | National quality and governance |
| 8 | `national_auditor_inspector` | National auditor/inspector | National read-only | National quality and governance |
| 9 | `center_manager` | Center manager | Assigned center | Center management |
| 10 | `reception_officer` | Reception officer | Assigned center/department | Reception |
| 11 | `screening_counselling_officer` | Screening/counselling officer | Assigned center/department | Screening and counselling |
| 12 | `collection_phlebotomy_officer` | Collection/phlebotomy officer | Assigned center/department | Collection |
| 13 | `laboratory_technician` | Laboratory technician | Assigned center/laboratory | Laboratory and components |
| 14 | `laboratory_approver_quality_officer` | Laboratory approver/quality officer | Assigned center/laboratory | Laboratory and components |
| 15 | `component_processing_officer` | Component-processing officer | Assigned center/department | Laboratory and components |
| 16 | `inventory_officer` | Inventory officer | Assigned center/storage | Center inventory and logistics |
| 17 | `logistics_cold_chain_officer` | Logistics/cold-chain officer | Assigned center/route/location | Center inventory and logistics |
| 18 | `center_haemovigilance_quality_officer` | Center haemovigilance/quality officer | Assigned center | Center quality and haemovigilance |
| 19 | `center_read_only_auditor` | Center read-only auditor | Assigned center read-only | Center quality and haemovigilance |
| 20 | `hospital_clinician_requester` | Hospital clinician/requester | Assigned hospital | Hospital operations |
| 21 | `hospital_blood_bank_officer` | Hospital blood-bank officer | Assigned hospital | Hospital operations |
| 22 | `compatibility_crossmatch_officer` | Compatibility/crossmatch officer | Assigned hospital/laboratory | Hospital operations |
| 23 | `transfusion_nurse_officer` | Transfusion nurse/officer | Assigned hospital/ward | Hospital operations |
| 24 | `hospital_haemovigilance_officer` | Hospital haemovigilance officer | Assigned hospital | Hospital operations |
| 25 | `hospital_read_only_reviewer` | Hospital read-only reviewer | Assigned hospital read-only | Hospital operations |
| 26 | `donor` | Donor | Own records | Donor home (Flutter) |

## 6. Platform and national permission matrix

| Role | System | Structure | Assignments | Donor/center operations | Lab/release | Inventory/logistics | Hospital/transfusion | Quality/HV | Engagement/content | Reports/export | Audit |
| --- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| Super administrator | M | M | M | V | V | V | V | V | V | V | V |
| ICT/security operator | M | V | — | — | — | — | — | — | — | V | V |
| National operations administrator | — | M | M | M | V | M | V | V | M | M | V |
| National quality/haemovigilance officer | — | V | V | V | V | V | V | A | V | M | V |
| National inventory/logistics coordinator | — | V | V | V | V | M | V | V | — | M | V |
| National donor engagement/content officer | — | V | — | V | — | V | — | — | M | M | V |
| Data-protection/governance officer | — | V | V | V | V | V | V | V | V | M | V |
| National auditor/inspector | — | V | V | V | V | V | V | V | V | V | V |

Clarifications:

- Super administrator can configure infrastructure and role foundations but has view-only operational awareness by default. It cannot perform clinical release or transfusion decisions without a separately approved scoped clinical assignment.
- ICT/security operator cannot browse donor or patient records merely to diagnose infrastructure. Support access requires a controlled purpose and minimum-necessary process.
- National auditor/inspector is read-only; export remains separately controlled and purpose logged.
- National quality approval applies only to approved quality/haemovigilance decisions and never substitutes for a required center-level laboratory releaser.

## 7. Blood-center permission matrix

| Role | Staff/structure | Reception | Appointments | Screening | Collection | Lab tests | Release | Components | Inventory | Logistics | Quality/HV | Reports/audit |
| --- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| Center manager | M | M | M | V | V | V | V | V | M | M | V | M |
| Reception officer | — | O | O | V | — | — | — | — | — | — | — | — |
| Screening/counselling officer | — | V | V | O | — | — | — | — | — | — | V | V |
| Collection/phlebotomy officer | — | V | V | V | O | — | — | — | V | — | V | V |
| Laboratory technician | — | — | — | V | V | O | — | V | V | — | V | V |
| Laboratory approver/quality officer | — | — | — | V | V | V | A | V | V | — | M | M |
| Component-processing officer | — | — | — | — | V | V | — | O | V | V | V | V |
| Inventory officer | — | — | — | — | V | V | — | V | O | V | V | M |
| Logistics/cold-chain officer | — | — | — | — | — | — | — | V | V | O | V | V |
| Center haemovigilance/quality officer | V | V | V | V | V | V | V | V | V | V | M | M |
| Center read-only auditor | V | V | V | V | V | V | V | V | V | V | V | V |

Clarifications:

- Center manager coordinates the center but cannot act as the sole laboratory releaser, compatibility officer, or transfusion officer without a separate approved assignment and competency.
- Laboratory technician records tests but cannot approve their own release where independent approval is required.
- Laboratory approver can decide release only after all approved conditions pass and cannot bypass quarantine blockers.
- Inventory officer cannot change laboratory results or release status.
- Center read-only auditor cannot mutate, approve, sign, acknowledge, or silently export unrestricted identified data.

## 8. Hospital permission matrix

| Role | Requests | Patient reference | Compatibility | Allocation/issue | Receipt | Transfusion outcome | Return/final disposition | Hospital HV | Reports/audit |
| --- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| Hospital clinician/requester | O | O | V | V | V | O | V | V | V |
| Hospital blood-bank officer | M | V | V | O | O | V | O | V | M |
| Compatibility/crossmatch officer | V | O | O | V | V | V | V | V | V |
| Transfusion nurse/officer | V | O | V | V | O | O | O | O | V |
| Hospital haemovigilance officer | V | V | V | V | V | V | V | M | M |
| Hospital read-only reviewer | V | V | V | V | V | V | V | V | V |

Hospital authority is organization scoped. Patient-identifying fields remain minimum necessary. Exact request, compatibility, emergency-release, bedside-verification, and outcome rules remain pending the hospital boundary ADR and approved policy.

## 9. Donor permission boundary

The donor can:

- Manage their own profile and preferences.
- View their own donor card, eligibility summary, donation history, recognition, notifications, and appointments.
- Discover approved centers, campaigns, articles, publications, schedules, and public guidance.
- Book, reschedule, or cancel eligible appointments through the supported API contract.

The donor cannot:

- Enter the staff command center.
- View another donor’s record.
- Mark themselves clinically eligible.
- Verify blood group.
- Record a donation, test, release, issue, transfusion, or quality decision.
- Obtain staff authority through Firebase or mobile registration.

## 10. Target permission-code groups

Current implemented permissions remain in `App\PermissionName`. Phase 5 adds governance and scoped-assignment permissions and reserves later-domain groups without claiming their workflows are complete.

| Group | Example stable permissions | Phase 5 use |
| --- | --- | --- |
| Organization | `organizations.view`, `organizations.manage`, `departments.view`, `departments.manage` | Implemented foundation |
| Assignments | `assignments.view`, `assignments.manage`, `assignments.approve` | Implemented foundation |
| System | `system_health.view`, `settings.manage`, `backups.manage` | Existing/extended foundation |
| Donor/reception | Existing donor, center, appointment, eligibility, donation permissions | Existing foundation |
| Laboratory | `laboratory.view`, `laboratory.tests.record`, `laboratory.release.approve` | Target codes; no release workflow claim |
| Components | `components.view`, `components.process`, `components.approve` | Target codes; later workflow |
| Logistics/cold chain | `logistics.view`, `logistics.manage`, `cold_chain.manage` | Target codes; later workflow |
| Hospital | `hospital_requests.view`, `hospital_requests.manage`, `compatibility.manage`, `blood_issue.manage`, `transfusions.record` | Target codes; later workflow |
| Quality/HV | `quality.view`, `quality.manage`, `haemovigilance.manage`, `recalls.manage` | Target codes; later workflow |

Target codes may be seeded with no reachable operational action until their module exists. Authorization tests must prove that a seeded permission does not expose absent or forbidden functionality.

## 11. Assignment and context permissions

- `assignments.view` permits viewing assignments inside the actor’s administrative scope.
- `assignments.manage` permits creating, suspending, and revoking ordinary assignments in scope.
- `assignments.approve` is reserved for approved high-risk assignment decisions.
- A user cannot select another person’s assignment as active context.
- An active context cannot enlarge the permissions contained in its role profile.
- A compatibility account without migrated assignments uses the current fallback only during the controlled transition.

## 12. Separation-of-duty rules

### 12.1 Always enforced

- Account deactivation denies all direct and role permissions.
- Assignment deactivation/expiry/revocation denies scoped authority.
- Technical super-administration does not imply clinical authority.
- Cross-center and cross-hospital record access requires an effective assignment or approved national purpose.
- Users cannot approve their own high-risk role assignment.
- Audit and read-only profiles cannot mutate operational records.

### 12.2 Record-level controls for later modules

- Collector and unauthorized relabel/replacement actor must be separated according to approved exception policy.
- Tester cannot be the sole verifier/releaser of the same affected component where independent release is required.
- Blood-group correction requires a different authorized reviewer when policy requires it.
- Emergency release, manual adjustment, disposal, recall closure, and high-risk configuration require explicit authority, reason, confirmation, and audit.
- Compatibility and transfusion actors must satisfy the approved hospital and bedside-verification rules.

Holding two profiles does not automatically violate policy; acting in conflicting steps on the same record may. Later domain actions must evaluate the actual actor history and required independent approvals.

## 13. Role assignment rules

- Role profile must be valid for the selected organization type.
- Center and hospital roles require an assigned organization scope.
- Department and location, when supplied, must belong to that organization.
- Assignment dates must be coherent and cannot create an exact duplicate effective assignment.
- Actor must have assignment-management authority for that scope.
- Reason is mandatory for high-risk, privileged, suspension, and revocation changes.
- Assignment changes are transactional and audited.
- Assignment records are never physically deleted to erase accountability.
- A later reassignment creates a new effective record when history would otherwise be lost.

## 14. Dashboard mapping and concise information contract

| Dashboard | Role profiles | Required concise information |
| --- | --- | --- |
| System control | Super administrator, ICT/security operator | Availability, failed jobs, integrations, security alerts, audit integrity, backup/restore, certificates, support incidents |
| National operations | National operations administrator | National stock, donor flow, collection, shortages, center performance, incidents, emergency coordination |
| National quality and governance | National quality/HV, DPO, national auditor | Events, recalls, CAPA, audit/privacy findings, overdue reviews, evidence queues |
| National inventory and logistics | National inventory/logistics coordinator | Stock, days of supply, expiry, transfers, cold-chain exceptions, hospital demand |
| Engagement and content | National engagement/content officer | Campaigns, audiences, delivery failures, publications, schedules, approvals |
| Center management | Center manager | Today’s donors, staffing, department queues, stock, incidents, alarms, unresolved exceptions |
| Reception | Reception officer | Arrivals, appointments, walk-ins, identity/duplicate checks, registration, check-in |
| Screening and counselling | Screening/counselling officer | Waiting donors, eligibility, deferrals, re-entry, counselling, overdue appointments |
| Collection | Collection/phlebotomy officer | Cleared donors, collection progress, identifiers, specimens, reactions, incomplete handoffs |
| Laboratory and components | Lab technician, lab approver, component officer | Specimens, testing, QC, discrepancy, quarantine/release, component lineage/yield as implemented |
| Center inventory and logistics | Inventory, logistics/cold-chain officers | Available/reserved stock, FEFO, expiry, locations, transfer, dispatch/receipt, excursions, reconciliation |
| Center quality and haemovigilance | Center quality/HV, center auditor | Incidents, reactions, recalls, deviations, investigations, CAPA, overdue evidence |
| Hospital operations | Six hospital profiles | Requests, compatibility, allocation, issue, receipt, bedside verification, outcomes, return, reactions by authority |
| Donor home | Donor | Eligibility, next donation date, donor card, appointments, history, recognition, notifications, centers, campaigns |

Unsupported later-phase panels are omitted and the layout reflows. Fake metrics and nonfunctional quick actions are prohibited.

## 15. Verification matrix

Automated verification must prove:

- All twenty-six target profile codes and two compatibility-only role codes seed idempotently.
- Every profile receives only its documented implemented permissions.
- One user with different roles at different centers cannot carry a permission across scope.
- National operational, technical, audit, center, hospital, and donor boundaries remain distinct.
- Super administrator does not receive clinical release through technical bypass.
- Inactive account, assignment, organization, department, or location blocks authority.
- Assignment selection is ownership checked and tamper resistant.
- Dashboard configuration and metrics match the effective assignment.
- Read-only roles see no mutation controls and direct action requests are denied.
- Existing compatibility accounts and cloned-database records continue to work during migration.

Browser verification must cover the five persistent local compatibility accounts and representative factory-backed target profiles. Any persistent local account created for manual QA must be added to `docs/security/local-demo-credentials.md`.

## 16. Implemented Phase 5 evidence

- All twenty-six target profile codes and two compatibility-only transition codes seed idempotently.
- Explicit permission mappings replace blanket technical bypass; super administrators do not inherit unregistered or later clinical authority.
- Effective assignment status, dates, organization state, ownership, role permission, and active context are enforced before scoped authority.
- Twenty-five staff target profiles resolve to exactly thirteen shared Laravel dashboard configurations; donor remains mobile/API-only.
- Five compatibility accounts are seeded and documented for local construction testing.
- Permission, assignment, isolation, seeder, dashboard, policy, and regression checks pass in the complete 213-test, 2,473-assertion suite.
- Headed Chromium QA at 1600×900 covers the five compatibility boundaries, assignment switching, collapsed navigation, dark/light presentation, national inventory aggregation, and browser errors. All four discovered defects were fixed and rechecked.

This evidence verifies the Phase 5 authorization and dashboard foundation only. It does not activate later laboratory release, component approval, compatibility, blood issue, transfusion, recall, or other unapproved clinical actions.

## 17. Approval requirements

Product implementation direction is approved by the project owner on 2026-08-12.

Before production clinical authority, obtain and record:

- NBTS operations approval of hierarchy, scope, center capabilities, department ownership, and escalation.
- Laboratory/quality approval of test, verification, release, component, and competency authority.
- Hospital/clinical approval of request, patient-reference, compatibility, emergency issue, bedside verification, and transfusion authority.
- DPO/legal approval of minimum-necessary access, purpose logging, export, retention, and patient/donor data boundary.
- Ministry/executive approval where national policy requires it.

Until those approvals exist, later-phase clinical profiles default to no executable clinical action even if their target permission codes are present.
