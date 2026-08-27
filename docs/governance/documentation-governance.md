# NBTS Documentation Governance Rules

Last updated: 2026-08-11  
Document ID: `DOC-GOV-001`  
Status: Active roadmap control

## 1. Purpose

This document defines how NBTS documentation must be created, updated, reviewed, approved, and used.

It exists to prevent:

- Planned work being reported as completed.
- Legacy code being treated as verified current behavior.
- Clinical or laboratory assumptions being treated as approved policy.
- Conflicting workflow, API, database, and implementation records.
- Safety-critical changes being implemented without evidence, approval, rollback, and traceability.
- Secrets, private credentials, donor data, patient data, or production access details being placed in documentation.

These rules apply to all Markdown files in `docs/`, including roadmap, workflow, architecture, API, operations, clinical-safety, quality, testing, and rollout documents.

---

## 2. Documentation authority

Different files answer different questions. A file must not take over the responsibility of another file.

| Question | Authoritative document |
|---|---|
| What is planned, pending, blocked, or completed? | `docs/planning/task.md` |
| How should the target system operate? | `docs/planning/workflow.md` |
| What has actually been implemented and verified? | `docs/evidence/achievement.md` |
| What API contract is currently supported? | `docs/technical/api.md` |
| How is the system deployed, backed up, restored, and supported? | `docs/operations/runbook.md` |
| Which documents exist, are missing, or require review? | `docs/governance/documentation-register.md` |
| Which safety hazards and controls apply? | `docs/clinical-safety.md` and `docs/risk-register.md` |
| Which data fields, identifiers, codes, and ownership rules apply? | `docs/data-dictionary.md` |
| Why was an important design decision made? | `docs/adr/ADR-*.md` |
| What do approved clinical or operational authorities require? | Approved policy/SOP references recorded in the relevant controlled document |

### 2.1 Source-code and database truth

Documentation does not override implemented code, migrations, database constraints, automated tests, or deployed configuration.

When documentation and implementation disagree:

1. Do not silently edit one side to make them appear consistent.
2. Record the conflict in `docs/planning/task.md`.
3. Identify whether the implementation or document is wrong.
4. Obtain the required technical, clinical, laboratory, quality, privacy, or operational approval.
5. Correct the affected files and implementation together.
6. Record the verified result in `docs/evidence/achievement.md`.

### 2.2 Clinical-policy boundary

A rule is not official merely because it appears in a system document.

The following require formal approval and versioning before production use:

- Donor eligibility and deferral rules.
- Required laboratory tests and test algorithms.
- Confirmatory and repeat-testing rules.
- Component catalog and product codes.
- Component shelf life and storage limits.
- Quarantine and release criteria.
- Dual-authorization requirements.
- Compatibility and emergency-release rules.
- Cold-chain limits and excursion decisions.
- Recall, look-back, notification, and retention rules.
- Hospital-request data requirements.
- National identifiers and barcode standards.
- RTO, RPO, SLA, and downtime rules.

---

## 3. Core status rules

Use these status markers consistently:

- `[ ]` Not started or not proven.
- `[-]` In progress.
- `[x]` Completed and verified.
- `[!]` Blocked by an external decision, credential, service, policy, supplier, or environment.
- `[~]` Implemented but awaiting required operational or clinical validation.
- `[-deprecated-]` Retained only for historical or migration reference.

### 3.1 Meaning of completed

A task may be marked `[x]` only when all required evidence exists.

For a standard feature, completion normally requires:

- Implementation exists in NBTS-NEW.
- Authorization and center scope are enforced.
- Database/API effects are defined.
- Automated tests pass.
- Browser/device checks pass where a UI exists.
- Documentation is updated.
- Known limitations are recorded.
- No unresolved critical defect remains.

For a safety-critical feature, completion additionally requires:

- Approved clinical/laboratory/quality rule version.
- Positive and negative-path tests.
- Bypass-prevention tests.
- Separation-of-duties tests where required.
- Audit evidence.
- Traceability evidence.
- Failure, rollback, downtime, or reconciliation evidence.
- Named operational owner.
- Required clinical, laboratory, quality, hospital, privacy, or Ministry approval.
- Pilot or operational validation where the completion gate requires it.

A model, migration, screen, endpoint, copied legacy module, or isolated unit test is not enough by itself.

### 3.2 Achievement rule

`docs/evidence/achievement.md` is evidence-only.

Do not add:

- Ideas.
- Planned features.
- Unexecuted tests.
- Legacy behavior that was not reverified.
- Screenshots without a working implementation.
- Statements such as “complete” when only backend, UI, API, or documentation work exists.
- Clinical claims without the required approval.

---

## 4. Requirement identification

Every significant roadmap requirement must have a stable ID.

Recommended prefixes:

| Prefix | Area |
|---|---|
| `DOC` | Documentation |
| `GOV` | Governance |
| `CTR` | Center structure |
| `ROLE` | Roles and permissions |
| `DON` | Donor services |
| `SCR` | Screening and deferral |
| `COL` | Collection |
| `ID` | Donation identifiers and barcodes |
| `LAB` | Laboratory |
| `REL` | Quarantine and release |
| `CMP` | Component production |
| `INV` | Inventory |
| `CC` | Cold chain |
| `LOG` | Logistics and dispatch |
| `HSP` | Hospital requests |
| `XMT` | Compatibility and crossmatch |
| `TRF` | Transfusion |
| `HV` | Haemovigilance |
| `REC` | Recall and look-back |
| `QMS` | Quality management |
| `SEC` | Security and privacy |
| `DATA` | Data governance |
| `API` | API contract |
| `INT` | Interoperability |
| `OFF` | Offline and downtime |
| `DR` | Disaster recovery |
| `RPT` | Reports and KPIs |
| `UX` | User experience |
| `ROLLOUT` | Pilot and rollout |

Format:

```text
AREA-SUBJECT-NNN
```

Examples:

```text
LAB-RESULT-001
REL-QUARANTINE-001
CMP-LINEAGE-001
HSP-REQUEST-001
REC-LOOKBACK-001
```

Do not reuse an ID for a different requirement after publication.

---

## 5. Priority and safety classification

Every significant requirement must state both priority and safety class.

### Priority

- `Must`: required before the affected workflow can enter production.
- `Should`: required during controlled scale-up after the core safety chain is stable.
- `Could`: optimization after safety, data quality, adoption, and operations are proven.

### Safety class

- `Critical`: failure can release unsafe blood, misidentify a donor/sample/component/patient, lose traceability, or delay emergency care.
- `High`: failure can cause serious shortage, wastage, privacy harm, prolonged outage, or incorrect operational/clinical decisions.
- `Standard`: important service, usability, engagement, content, or administration without direct release/transfusion authority.

Priority and difficulty are different. A difficult `Must–Critical` item must be phased and validated, not removed because it is difficult.

---

## 6. Required task fields

Every major item in `docs/planning/task.md` must include:

```md
### REQUIREMENT-ID — Requirement title

Status: [ ]
Priority: Must | Should | Could
Safety: Critical | High | Standard
Owner:
Approvers:
Dependencies:
Affected centers/departments:
Affected documents:

Implementation:
- [ ]

Verification:
- [ ]

Operational validation:
- [ ]

Completion gate:
- [ ]

Known constraints:
```

A requirement must not move to `[x]` until its completion gate is satisfied.

---

## 7. Required achievement fields

Every major entry in `docs/evidence/achievement.md` must include:

```md
## YYYY-MM-DD — Milestone title

Status: completed
Requirement IDs:
Scope:
Safety classification:
Clinical/operational rule version:

Delivered:
- ...

Main implementation:
- ...

Database/API impact:
- ...

Automated verification:
- ...

Browser/device verification:
- ...

Traceability or reconciliation evidence:
- ...

Operational drill or validation:
- ...

Approvals:
- ...

Known limitations:
- ...

Rollback or recovery:
- ...

Next dependent task:
- ...
```

Use `Not applicable` only when it is genuinely not applicable. Do not omit a required section to hide missing evidence.

---

## 8. Workflow-document rules

`docs/planning/workflow.md` defines the target operating model.

Every workflow must show:

- Trigger.
- Responsible role and department.
- Center or national scope.
- Required inputs.
- State transitions.
- Authorization.
- Separation of duties.
- Audit event.
- Notifications.
- Failure and exception paths.
- Offline/downtime behavior where relevant.
- Final accounted outcome.
- Related requirement IDs.
- Related policy/SOP version.
- Completion evidence location.

Safety-critical workflows must include explicit “cannot happen” invariants.

Examples:

- A quarantined component cannot appear as available.
- A failed or incomplete test cannot satisfy release.
- A component cannot be allocated to two active requests.
- A tester cannot be the sole releaser where independent approval is required.
- An issued component must reach a final disposition.
- A center-scoped user cannot access another center without an active assignment.
- Offline synchronization cannot automatically release a component.

---

## 9. Cross-document update rule

A feature change may require several documents to change together.

| Change type | Documents that must be reviewed |
|---|---|
| New feature or workflow | `docs/planning/task.md`, `docs/planning/workflow.md`, relevant module document |
| Completed implementation | `docs/planning/task.md`, `docs/evidence/achievement.md` |
| API request/response change | `docs/technical/api.md`, `docs/planning/workflow.md`, Flutter contract tests, `docs/planning/task.md`, `docs/evidence/achievement.md` |
| Database/entity/status change | `data-dictionary.md`, `domain-model.md`, migrations, API docs, tests |
| Clinical/laboratory rule change | `clinical-safety.md`, relevant workflow, `risk-register.md`, change-control record, tests |
| Role or permission change | `docs/security/roles-and-permissions.md`, workflow, authorization tests |
| Center structure change | `docs/operations/center-operating-model.md`, roles, dashboards, reports |
| Deployment or infrastructure change | `architecture.md`, `docs/operations/runbook.md`, `disaster-recovery.md`, security review |
| New external integration | `interoperability.md`, `docs/technical/api.md`, security/privacy, reconciliation and monitoring |
| Incident or failure | `incident-response.md`, risk register, CAPA, task/achievement where changes are implemented |
| KPI or report change | `kpi-dictionary.md`, report requirements, source/reconciliation tests |
| Major design decision | New or updated ADR |

### 9.1 Required update order

Before implementation:

1. Create or update the requirement in `docs/planning/task.md`.
2. Update the target workflow.
3. Update the risk/safety record.
4. Update the data/API/architecture document if affected.
5. Record the decision in an ADR when necessary.
6. Obtain required approval.

During implementation:

1. Keep the task status accurate.
2. Add tests with the implementation.
3. Do not report partial work as complete.
4. Record discovered limitations and dependencies.

After implementation:

1. Run all required verification.
2. Update API/data/operations documents.
3. Complete browser/device or operational validation.
4. Mark the task complete only after the gate passes.
5. Add the evidence entry to `docs/evidence/achievement.md`.

---

## 10. Review and approval

Each controlled document must identify its owner and required reviewers.

Suggested owners:

| Document area | Primary owner | Required reviewers |
|---|---|---|
| System roadmap | Product owner | Operations, clinical safety, ICT |
| Clinical safety | Clinical safety officer | Laboratory, quality, hospital representatives |
| Laboratory and release | Laboratory lead | Quality, clinical safety, ICT |
| Components and inventory | Blood operations lead | Quality, logistics, laboratory |
| Hospital and transfusion | Clinical/hospital lead | Blood bank, quality, clinical safety |
| Security and privacy | ICT security/DPO | Legal, product owner |
| Operations and DR | ICT operations | Security, management, center representatives |
| API and interoperability | Technical lead | Mobile, integration partners, security |
| Testing and validation | QA lead | Domain owners, clinical safety |
| Risk and CAPA | Quality lead | Relevant operational owner |

Approval must be recorded by name/role, date, document version, and scope. “Reviewed” and “approved” are not the same.

---

## 11. Change-control rules

A formal change record is required when a change affects:

- Eligibility.
- Deferral.
- Test algorithms.
- Component labels or codes.
- Expiry calculation.
- Quarantine.
- Release.
- Compatibility.
- Emergency issue.
- Cold-chain limits.
- Recall/look-back.
- Patient or donor identifiers.
- Retention.
- Privacy purpose.
- External data sharing.
- Offline authority.
- Disaster recovery.
- Production infrastructure.

Every change record must include:

- Change ID.
- Reason.
- Affected requirement IDs.
- Risk assessment.
- Before/after behavior.
- Data migration impact.
- API impact.
- Test plan.
- Validation plan.
- Rollback plan.
- Training impact.
- Documentation impact.
- Required approvals.
- Release date.
- Post-release review.

No direct production edit may replace the controlled process.

---

## 12. ADR rules

Create an Architecture Decision Record when a decision is expensive to reverse, affects multiple modules, changes safety boundaries, or creates a long-term dependency.

Examples:

- National donation identifier standard.
- Barcode standard.
- Whether `blood_units` represents collection containers or final components.
- Component parent-child model.
- Laboratory integration pattern.
- Hospital-integration boundary.
- FHIR profile strategy.
- Offline identifier generation.
- Event/outbox architecture.
- Audit-integrity model.
- Data-retention design.
- Vendor-hosting or cloud decision.

ADR status:

- Proposed.
- Accepted.
- Superseded.
- Rejected.
- Deprecated.

Never delete an accepted ADR. Supersede it with a new ADR.

---

## 13. Data and privacy rules

Documentation must never contain:

- Passwords.
- API secrets.
- Private keys.
- Firebase service-account JSON.
- Production tokens.
- Full donor or patient records.
- NIDA numbers.
- Unmasked phone/email lists.
- Private infrastructure access details.
- Recovery codes.
- Real production database dumps.

Use synthetic examples and clearly label them as examples.

Data documents must define:

- Data owner.
- Lawful purpose.
- Minimum necessary fields.
- Source.
- Allowed roles.
- Retention.
- Correction method.
- Audit requirement.
- Export rules.
- De-identification rules.
- Integration mapping.

---

## 14. Naming and file-format rules

- Use lowercase kebab-case filenames.
- Use `.md`.
- Keep one primary purpose per file.
- Start every controlled document with title, last-updated date, document ID, owner, status, and approval state.
- Use stable machine codes in examples.
- Do not translate database status codes.
- English may remain the base documentation language, with Kiswahili summaries or separate approved translations where needed.
- Use relative links between files.
- Do not duplicate large sections; link to the authoritative document.
- Add diagrams as Mermaid only when the repository renderer supports them; always include a text explanation.
- Use tables for matrices, not for long narrative.
- Record unresolved decisions explicitly.

Recommended header:

```md
# Document title

Last updated: YYYY-MM-DD
Document ID:
Owner:
Status: Draft | Review | Approved | Superseded
Approval:
Related requirements:
```

---

## 15. Document lifecycle

1. **Draft** — content is being prepared and is not authoritative.
2. **Review** — technical/domain stakeholders are checking it.
3. **Approved** — required approvers accepted the version.
4. **Active** — used by implementation and operations.
5. **Superseded** — replaced by a newer controlled version.
6. **Archived** — retained for history but not current use.

Safety-critical documents must not move from Draft directly to Active.

---

## 16. Quality checks for every document

Before accepting a document:

- [ ] Purpose is clear.
- [ ] Owner is named.
- [ ] Status is accurate.
- [ ] Requirement IDs are linked.
- [ ] Current and target behavior are distinguished.
- [ ] Assumptions are labelled.
- [ ] Missing approvals are visible.
- [ ] Safety and privacy implications are covered.
- [ ] No secret or personal data is present.
- [ ] Links resolve.
- [ ] Terms and codes match the data dictionary.
- [ ] Completion claims match `docs/evidence/achievement.md`.
- [ ] Date and version are updated.
- [ ] Superseded content is not presented as active.

---

## 17. Non-negotiable documentation rules

1. Never mark a feature complete without direct evidence.
2. Never treat copied legacy code as verified implementation.
3. Never treat a draft clinical rule as approved policy.
4. Never allow `docs/planning/task.md`, `docs/planning/workflow.md`, and `docs/evidence/achievement.md` to contradict each other silently.
5. Never remove an API field without coordinated versioning.
6. Never hide a limitation to improve the appearance of progress.
7. Never store secrets or real personal health data in documentation.
8. Never deploy a safety-critical rule without change control, tests, approval, and rollback.
9. Never measure success only by units collected; include safety, quality, utilization, availability, wastage, traceability, and adverse-event indicators.
10. Never call the platform a complete national blood-management system until the required laboratory, release, component, hospital, cold-chain, haemovigilance, resilience, and rollout gates are proven.
