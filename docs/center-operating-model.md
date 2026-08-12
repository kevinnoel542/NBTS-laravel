# NBTS center operating model

Last updated: 2026-08-12  
Document ID: `CTR-OPERATING-001`  
Owner: NBTS operations / Product owner  
Status: Review  
Approval: Product implementation direction approved on 2026-08-12; final Ministry/NBTS hierarchy, center capabilities, staffing, clinical competency, and escalation approval remains pending.  
Related requirements: `GOV-STRUCT`, `GOV-DEPT`, `GOV-ROLE`, `GOV-SOD`, `GOV-VIS`, `GOV-OVERVIEW`  
Related ADRs: `ADR-005-hospital-integration-boundary.md` remains pending.  
Related policies/SOPs: Current approved organization, staffing, competency, quality, laboratory, hospital, downtime, and emergency policies must be linked before production acceptance.

## 1. Purpose

This document defines how national, regional/zonal, blood-center, hospital-interface, department, and work-location scope is represented and used by staff assignments, authorization, navigation, dashboards, queues, reports, audit evidence, and escalation.

It provides an additive migration path from the current `blood_centers` and `center_staff` compatibility records. It does not assert an unapproved national structure or grant clinical authority.

## 2. Operating principles

- Organization structure and staff authority are separate concerns: a unit may exist without granting any user access.
- Every operational record has an accountable organization/center context where applicable.
- A center can expose only capabilities approved for its type and active status.
- One person has one identity account and may hold multiple effective-dated assignments.
- The active assignment determines the current role, organization, department, navigation, dashboard, queues, and allowed actions.
- National visibility does not remove center accountability or separation of duties.
- Technical authority does not imply clinical authority.
- Assignment and structure history is retained; operational records are not orphaned by deactivation or reorganization.

## 3. Target hierarchy

The target hierarchy is:

`National NBTS → approved zone/region where applicable → blood center or hospital interface → department → work/storage location`

The zone/region level is optional until formal current-state discovery confirms it. The implementation must support hierarchy without forcing an invented regional layer.

### 3.1 Organization-unit types

| Type | Purpose | May contain |
| --- | --- | --- |
| National | NBTS-wide governance and coordination | Zones/regions, centers, approved national departments |
| Zone/region | Optional approved coordination layer | Centers and regional departments |
| Blood center | Collection and approved blood-service facility | Departments, work/storage locations, mobile teams |
| Hospital interface | Approved hospital or hospital blood-bank boundary | Hospital departments and work locations |

Departments and work locations are modeled explicitly rather than being inferred from free text.

### 3.2 Stable identity

Each organization unit requires:

- Stable unique code.
- Official name and optional short name.
- Type and parent unit.
- Active lifecycle state.
- Effective dates where required.
- Approved contacts and escalation ownership.
- Link to the existing `blood_centers` record when the unit represents a blood center.

Codes remain stable after creation. A rename changes display text, not identity.

## 4. Organization lifecycle

| State | Meaning | Access behavior |
| --- | --- | --- |
| Active | Approved for current operation | Assignments and configured capabilities may be used |
| Suspended | Temporarily prohibited from operational processing | New operational actions blocked; authorized evidence remains readable |
| Temporarily closed | Facility is not accepting the affected service | Service-specific queues/actions blocked or redirected according to approved continuity procedure |
| Retired | No longer operational | New assignments and transactions blocked; history retained |

State changes require effective date, reason, actor, and audit event. Retirement does not delete related centers, assignments, audit records, or domain history.

## 5. Candidate center types and capability control

The following are implementation-ready candidate types, not final policy claims:

| Candidate type | Candidate responsibility |
| --- | --- |
| Full collection/testing/processing center | Collection plus approved testing, component, storage, and distribution capabilities |
| Collection-only site or mobile team | Donor-facing collection and controlled handoff only |
| Testing/processing hub | Approved laboratory and/or component processing for linked collection sites |
| Storage/distribution hub | Approved storage, inventory balancing, dispatch, and receipt |
| Hospital blood-bank interface | Hospital request, compatibility, receipt, issue, transfusion, return, and haemovigilance boundary as approved |

Center type never grants functionality by itself. Capabilities are explicit and effective-dated. Candidate capabilities include:

- Donor registration and reception.
- Screening and counselling.
- Whole-blood collection.
- Apheresis collection.
- Specimen receipt and laboratory testing.
- Independent verification and release.
- Component processing.
- Storage and reservation.
- Transfer, dispatch, transport, and receipt.
- Hospital request and issue.
- Compatibility/crossmatch.
- Transfusion outcome and return.
- Quality, haemovigilance, recall, and CAPA.
- Mobile/offline operation.

Unapproved capabilities remain unavailable even when a user’s role profile contains the related permission.

## 6. Departments

The target department catalogue supports:

- Management.
- Reception and donor registration.
- Screening and counselling.
- Collection/phlebotomy.
- Laboratory.
- Laboratory quality/release.
- Component processing.
- Inventory and storage.
- Logistics and cold chain.
- Quality and haemovigilance.
- Donor engagement and content.
- Data protection/governance.
- ICT and support.
- Audit and inspection.
- Hospital clinical request.
- Hospital blood bank/compatibility.
- Transfusion services.

Each department has a stable code, owning organization unit, active status, accountable owner, and escalation route. A small center may combine operational departments, but a combined department cannot bypass record-level independent approval.

## 7. Work and storage locations

A work location represents an operational place within an organization/department, such as:

- Reception desk.
- Screening room.
- Collection chair/area.
- Specimen-receipt bench.
- Testing laboratory.
- Quarantine storage.
- Released inventory refrigerator/freezer.
- Component-processing room.
- Dispatch area.
- Vehicle or mobile team.
- Hospital blood bank.
- Transfusion ward interface.

Work locations require a stable code, type, organization, optional department, lifecycle state, and any approved equipment/storage relationship. Free-text location remains descriptive only and cannot replace authoritative location identity in safety-critical workflows.

## 8. Staff assignment model

### 8.1 Assignment fields

Every assignment records:

- User.
- Operational role profile.
- Organization unit.
- Optional department.
- Optional work location.
- Optional shift label.
- Effective start and end time.
- Status.
- Approver.
- Reason.
- Revoker and revocation time where applicable.
- Created and updated timestamps.

### 8.2 Assignment states

`draft → active → suspended → active`  
`draft → active → expired`  
`draft → active → revoked`

The initial Phase 5 implementation uses active, suspended, expired, and revoked states. Draft/approval workflow may remain an administration action boundary until formal dual-approval policy is supplied.

### 8.3 Effective access

An assignment is effective only when:

- The account is active.
- The assignment is active.
- The current time is within its effective dates.
- Its organization unit is active.
- Its department and location are active when specified.
- The role profile is active.
- Required competency is present when an approved rule exists.
- The requested record/action belongs to the assignment scope.
- No separation-of-duty rule blocks the action.

### 8.4 Multiple assignments

A person may be assigned to:

- Several roles at one center.
- One role at several centers.
- Different roles at different centers.
- An approved national assignment and a center assignment.

Each authority remains attached to its assignment. A user who is an inventory officer at Center A and a reception officer at Center B cannot use inventory authority at Center B.

### 8.5 Active assignment context

- If exactly one effective staff assignment exists, it becomes the active context automatically.
- If several exist, the most recent valid session selection is used; otherwise the user selects one before entering scoped work.
- The selection stores only an assignment identifier in the session.
- Every request reloads and validates the assignment; session state is never trusted as authorization evidence.
- Switching assignment updates navigation, dashboard configuration, center/hospital label, department label, queue scope, and action permissions.
- An invalid, expired, suspended, revoked, or foreign assignment selection is rejected and replaced with a safe valid context or an unassigned state.
- National scope is represented by a real national assignment rather than an unrestricted client-selected value.

## 9. Assignment management

### 9.1 Create or activate

1. Authorized administrator selects an active user and organization scope.
2. System displays only role profiles permitted for that organization type.
3. Department/location choices are constrained to the selected organization.
4. Effective dates, reason, and required approval are validated.
5. Duplicate or contradictory assignment requests are rejected.
6. Assignment is written transactionally and audited.
7. User access changes on the next authorization check.

### 9.2 Suspend, revoke, or expire

- Suspension is reversible and requires reason.
- Revocation is final for that assignment record; a later appointment creates a new assignment.
- Expiry follows the effective end time and does not require deletion.
- Current sessions referencing ineffective assignments lose scoped access immediately.
- Historical actions continue to reference the original user and assignment context where recorded.

### 9.3 Self-assignment and privileged changes

- Users cannot approve their own high-risk clinical assignment.
- Technical super administrators do not gain clinical authority merely by managing infrastructure.
- Assignment management requires a dedicated permission and scope check, not only visibility of the Administration workspace.
- High-risk role/profile changes require an explicit reason and audit metadata.

## 10. Center and record scope

### 10.1 National users

National assignments may view or coordinate approved national data according to their profile. National scope does not bypass clinical record-level approval, minimum-necessary privacy, or center accountability.

### 10.2 Center users

Center assignments can access only records belonging to the active center and permitted department/purpose. Donor access can be established through preferred center, appointment, donation, or another approved operational relationship.

### 10.3 Hospital users

Hospital assignments can access only their hospital’s requests, allocated/issued components, approved patient references, receipt/transfusion/return records, and authorized aggregate information.

### 10.4 Auditors

Auditors are read-only, purpose logged, and export controlled. Audit visibility does not grant operational mutation.

### 10.5 Donors

Donors do not receive staff assignments. Donor authorization remains ownership-based through the mobile API.

## 11. Separation of duties

The following requirements are conservative target controls pending final policy approval:

- A collector cannot silently replace or relabel a specimen after collection.
- A laboratory tester cannot be the sole verifier and releaser of the same component where independent control is required.
- Release cannot bypass incomplete, reactive, discrepant, failed-QC, expired, recalled, or unresolved cold-chain conditions.
- Blood-group correction after staff verification requires elevated permission, reason, confirmation, and independent review.
- Emergency release, manual inventory adjustment, disposal, recall closure, and high-risk configuration changes require explicit authority and audit.
- Super-administrator technical access does not automatically grant clinical release authority.

Phase 5 records role and assignment context. Later domain actions must enforce actor-versus-reviewer separation on the affected record; permanent role incompatibility must not be invented where record-level independent action is the actual policy requirement.

## 12. Dashboard and queue behavior

The active assignment maps to one of thirteen staff dashboard configurations. Every dashboard:

- Shows the active role, organization, department, and responsibility summary.
- Uses only data from the effective scope.
- Places urgent, unsafe, blocked, and overdue work first.
- Links metrics to evidence queues.
- Hides unsupported or unauthorized sections and reflows without gaps.
- Uses role-appropriate quick actions.
- Includes clear empty, loading, failure, and unassigned states.

Center managers may see assigned-center summaries across permitted departments. Department users see only relevant queues. National users see approved aggregate/coordination views. Hospital users see hospital-scoped work. ICT users see system control without clinical authority.

## 13. Audit events

At minimum, record:

- `organization_unit.created`
- `organization_unit.updated`
- `organization_unit.status_changed`
- `department.created`
- `department.updated`
- `department.status_changed`
- `work_location.created`
- `work_location.updated`
- `work_location.status_changed`
- `staff_assignment.created`
- `staff_assignment.activated`
- `staff_assignment.suspended`
- `staff_assignment.revoked`
- `staff_assignment.expired`
- `staff_assignment.context_selected` only when required by risk/volume policy; ordinary navigation should not create excessive low-value audit records.

Audit metadata contains stable identifiers and safe before/after state, never passwords, tokens, recovery codes, or unnecessary health data.

## 14. Failure and exception handling

- User with no effective assignment receives a clear unassigned state and no operational data.
- Invalid assignment selection is rejected server-side.
- Suspended/retired scope blocks new actions and preserves history.
- Missing department/location prevents actions that require that context.
- Duplicate active assignment is rejected under a transaction and lock.
- Backfill mismatch leaves the compatibility assignment active and records a reconciliation failure; it does not silently remove access.
- Dashboard query failure shows a scoped retryable error and never falls back to unrestricted data.

## 15. Offline and downtime behavior

Phase 5 does not grant offline clinical authority. During downtime, approved paper/manual continuity procedures remain authoritative. Assignment and organization changes require the online authoritative service unless a later approved offline design explicitly permits otherwise.

## 16. Compatibility migration

The deployed `center_staff` table remains immutable and operational during migration.

Migration sequence:

1. Create additive organization, department, location, and assignment tables.
2. Create the National NBTS organization unit and one organization unit for each existing blood center.
3. Link existing blood centers to their organization units.
4. Seed the target role-profile catalogue while retaining compatibility roles.
5. Backfill each active/inactive `center_staff` record into a corresponding assignment through an idempotent command or seeder, not a mixed schema/data migration.
6. Compare user, center, role/position, and active state counts.
7. Run existing and new authorization tests against both fresh and cloned database paths.
8. Switch navigation/dashboard context to the assignment service with compatibility fallback.
9. Remove fallback only in a later approved migration after rollback and operational evidence. No existing record is deleted during Phase 5.

## 17. Implemented Phase 5 foundation

The product-owner-approved implementation now includes:

- Additive organization-unit, department, work-location, staff-assignment, staff-competency, and blood-center-link schema.
- Seeded National NBTS and blood-center organization records with compact, unique short names.
- Standard department and work-location foundations without asserting unapproved center clinical capabilities.
- Transactional assignment creation, suspension, revocation, effective-date enforcement, duplicate prevention, actor-scope validation, and auditable reasons.
- Ownership-checked active assignment selection that recalculates permissions, center context, navigation, dashboard configuration, metrics, queues, and quick actions.
- An idempotent `nbts:backfill-staff-assignments` command with dry-run support and retained `center_staff` compatibility fallback.
- Automated cross-center, cross-account, expired/suspended assignment, hospital, national, audit, and technical isolation coverage.

The development database contains five organization units, thirty-eight departments, and six scoped assignments after backfill and demo seeding. No legacy assignment record was deleted.

## 18. Verification

- Hierarchy and lifecycle model tests.
- Assignment creation, suspension, expiry, revocation, and duplicate-prevention tests.
- User with several roles and centers receives no cross-scope permission leakage.
- National, center, hospital, audit, and technical isolation tests.
- Invalid session-context and direct URL bypass tests.
- Organization/department suspension removes access immediately.
- Existing `center_staff` backfill and reconciliation tests.
- Dashboard role/configuration selection and scoped metric tests.
- English/Kiswahili, desktop 1600×900, responsive, dark/light, keyboard, loading, empty, and browser-error checks.

Verification passed on 2026-08-12 as part of the complete 213-test, 2,473-assertion Laravel suite. Visible dashboard QA evidence is stored in `docs/evidence/phase-5-dashboard-qa/`.

## 19. Approval decisions still required

- Whether zone/region is used and its official codes.
- Official center types and capability assignments.
- Official department names, ownership, and escalation routes.
- Shift model and whether shift coverage affects authority.
- Competency codes, validity periods, and retraining rules.
- Which assignment changes require dual approval.
- Record-level separation rules for laboratory release, emergency issue, compatibility, transfusion, disposal, and recall closure.
- Hospital organization and patient-reference boundary.

Until approved, the implementation uses stable generic organization/assignment foundations and defaults to no later-phase clinical authority.
