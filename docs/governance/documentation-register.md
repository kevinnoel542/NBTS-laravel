# NBTS Documentation Register and Creation Roadmap

Last updated: 2026-08-27
Document ID: `DOC-REG-001`  
Owner: Product owner / Documentation owner  
Status: Active roadmap register

## 1. Purpose

This file is the master list of NBTS documents.

It shows:

- Which documents already exist.
- Which documents must be created.
- Why each document is needed.
- Which phase requires it.
- Who should own it.
- Whether it is a roadmap, evidence record, technical contract, clinical control, operational runbook, or governance record.

The register must be updated whenever a controlled document is added, renamed, split, merged, approved, superseded, or archived.

---

## 2. Recommended folder structure

```text
docs/
├── README.md
├── planning/
│   ├── task.md
│   └── workflow.md
├── overview/
│   └── system-overview.md
├── governance/
│   ├── documentation-governance.md
│   ├── documentation-register.md
│   ├── documentation-templates.md
│   └── ui-documentation-workstream.md
├── technical/
│   ├── api.md
│   ├── architecture.md
│   ├── domain-model.md
│   ├── data-dictionary.md
│   ├── interoperability.md
│   └── adr/
│       ├── README.md
│       ├── ADR-001-donation-identifier.md
│       ├── ADR-002-barcode-standard.md
│       ├── ADR-003-component-domain-model.md
│       ├── ADR-004-quarantine-release-authority.md
│       ├── ADR-005-hospital-integration-boundary.md
│       └── ADR-006-offline-authority.md
├── operations/
│   ├── center-operating-model.md
│   ├── runbook.md
│   ├── laboratory-and-release.md
│   ├── components-inventory-cold-chain.md
│   ├── hospital-and-transfusion.md
│   ├── haemovigilance-recall-capa.md
│   ├── quality-management.md
│   ├── offline-and-downtime.md
│   ├── disaster-recovery.md
│   ├── incident-response.md
│   ├── support-sla.md
│   ├── testing-and-validation.md
│   ├── kpi-dictionary.md
│   ├── training-and-competency.md
│   ├── rollout-and-acceptance.md
│   ├── donor-engagement.md
│   ├── public-content-governance.md
│   ├── vendor-exit-and-handover.md
│   └── glossary.md
├── security/
│   ├── roles-and-permissions.md
│   ├── local-demo-credentials.md
│   ├── clinical-safety.md
│   ├── risk-register.md
│   └── security-and-privacy.md
└── evidence/
    ├── achievement.md
    └── phase-specific QA folders
```

Do not create every file only to make the folder look complete. Create each file when its owner, purpose, inputs, and review path are known.

---

## 3. Existing core documents

These documents already form the current roadmap foundation.

| File | Purpose | Current action |
|---|---|---|
| `docs/planning/task.md` | Living implementation checklist and status | Keep current; add requirement IDs, safety classes, dependencies, approvals, and completion gates |
| `docs/planning/workflow.md` | Target operating model | Keep current; expand and synchronize with approved module documents |
| `docs/evidence/achievement.md` | Verified implementation evidence | Keep evidence-only |
| `docs/operations/runbook.md` | Current deployment, backup, restore, and operator procedures | Review and expand for production, monitoring, downtime, DR, and support |
| `docs/technical/api.md` | Current `/api/v1` and Flutter compatibility contract | Review and expand for staff, laboratory, hospital, device, event, and integration APIs |
| `docs/overview/system-overview.md` | Current and target system boundary | Keep synchronized with roadmap and approvals |
| `docs/operations/center-operating-model.md` | Center hierarchy, scope, and assignment model | Keep synchronized with role and dashboard work |
| `docs/security/roles-and-permissions.md` | Role catalogue, permissions, and separation of duties | Keep synchronized with policies, seeders, and tests |
| `docs/security/local-demo-credentials.md` | Local-only compatibility demo accounts | Keep local/test only; never add production credentials |

The uploaded roadmap proves that the first three documents exist. The existing project records also state that `docs/operations/runbook.md` and `docs/technical/api.md` were created; verify their current repository contents before treating them as complete.

---

## 4. Documents to create first

These are required before the safety-critical implementation phase.

### 4.1 Documentation control

| File | Priority | Owner | Purpose | Status |
|---|---|---|---|---|
| `README.md` | Must | Documentation owner | Entry point, document map, current phase, and quick links | `[x]` Active in `docs/` |
| `docs/governance/documentation-governance.md` | Must | Product/quality | Rules for evidence, status, approval, change control, and synchronization | `[x]` Active in `docs/` |
| `docs/governance/documentation-register.md` | Must | Documentation owner | Master document inventory and creation roadmap | `[x]` Active in `docs/` |
| `docs/governance/documentation-templates.md` | Must | Documentation owner | Standard templates for requirements, achievements, risks, ADRs, and approvals | `[x]` Active in `docs/` |
| `docs/governance/ui-documentation-workstream.md` | Must only when a two-agent workstream is active | Technical lead / documentation owner | Two-agent ownership, UI direction, backend contract, handoff, QA, and completion gates | `[!]` Not present; the current workstream is single-agent |

### 4.2 System and organization

| File | Priority | Owner | Purpose | Status |
|---|---|---|---|---|
| `docs/overview/system-overview.md` | Must | Product owner | Explain boundaries, users, modules, current foundation, target national platform, and benefits | `[~]` Review draft created; external domain approvals pending |
| `docs/security/roles-and-permissions.md` | Must | Security/product | National, center, hospital, technical, audit, and quality roles; permission matrix and separation of duties | `[~]` Review draft created; external domain approvals pending |
| `docs/operations/center-operating-model.md` | Must | NBTS operations | National → zone/region → center → department structure, center types, services, staffing, assignments, shifts, queues, dashboards, and escalation | `[~]` Review draft created; external domain approvals pending |
| `docs/technical/architecture.md` | Must | Technical lead | Laravel, Flutter, databases, queues, event/outbox, storage, integrations, monitoring, trust boundaries, and deployment topology | `[ ]` |

### 4.3 Safety and data foundation

| File | Priority | Owner | Purpose | Status |
|---|---|---|---|---|
| `docs/security/clinical-safety.md` | Must | Clinical safety officer | Safety case, critical invariants, release protections, patient/donor harm controls, and approval boundaries | `[ ]` |
| `docs/security/risk-register.md` | Must | Quality lead | Hazards, likelihood, impact, controls, owners, warning indicators, residual risk, and evidence | `[ ]` |
| `docs/technical/domain-model.md` | Must | Technical/domain lead | Donor, episode, collection, specimen, test, component, inventory, request, patient reference, issue, transfusion, recall, and audit relationships | `[ ]` |
| `docs/technical/data-dictionary.md` | Must | Data governance | Fields, identifiers, codes, states, ownership, validation, retention, privacy class, and source system | `[ ]` |
| `docs/governance/change-control.md` | Must | Product/quality | Clinical, privacy, operational, infrastructure, and emergency change process | `[ ]` |

### 4.4 Core blood-chain modules

| File | Priority | Owner | Purpose | Status |
|---|---|---|---|---|
| `docs/operations/laboratory-and-release.md` | Must | Laboratory/quality | Samples, test orders, test runs, results, repeats, QC, reagents, interpretation, quarantine, dual approval, and release | `[ ]` |
| `docs/operations/components-inventory-cold-chain.md` | Must | Blood operations | Parent-child component lineage, product catalog, expiry, FEFO, reservation, location, transfer, return, disposal, equipment, temperature, and excursion handling | `[ ]` |
| `docs/operations/hospital-and-transfusion.md` | Must | Clinical/hospital | Hospital requests, clinical justification, patient/specimen reference, compatibility, allocation, emergency release, issue, dispatch, receipt, bedside verification, transfusion, and final disposition | `[ ]` |
| `docs/operations/haemovigilance-recall-capa.md` | Must | Quality/haemovigilance | Donor reactions, recipient adverse events, investigation, recall, look-back, deviations, root cause, and CAPA | `[ ]` |

### 4.5 Validation and operational readiness

| File | Priority | Owner | Purpose | Status |
|---|---|---|---|---|
| `docs/operations/testing-and-validation.md` | Must | QA/clinical safety | Test strategy, safety validation, traceability drills, concurrency, bypass tests, browser/device, performance, integration, migration, and acceptance evidence | `[ ]` |
| `docs/security/security-and-privacy.md` | Must | ICT security/DPO | Access, MFA, encryption, key management, audit, DPIA, consent, retention, incident response, and data-subject handling | `[ ]` |
| `docs/operations/rollout-and-acceptance.md` | Must | Product/operations | Discovery, pilot, regional scale, national rollout, exit criteria, approvals, training, readiness, and formal acceptance | `[ ]` |

---

## 5. Documents required before regional scale

| File | Priority | Owner | Purpose | Status |
|---|---|---|---|---|
| `docs/operations/quality-management.md` | Should | Quality lead | SOP control, deviations, CAPA, EQA, internal audit, document control, and accreditation readiness | `[ ]` |
| `docs/technical/interoperability.md` | Should | Integration lead | FHIR/local profiles, DHIS2/HMIS/LIS, analyzers, sensors, acknowledgements, retries, reconciliation, and partner onboarding | `[ ]` |
| `docs/operations/offline-and-downtime.md` | Should | Operations/technical | Offline datasets, identifiers, device security, conflict handling, server revalidation, downtime forms, and reconciliation | `[ ]` |
| `docs/operations/disaster-recovery.md` | Should | ICT operations | RTO/RPO, off-site backups, restore procedure, recovery environment, exercises, evidence, and reopening controls | `[ ]` |
| `docs/operations/incident-response.md` | Should | Security/operations | Severity, command roles, containment, communication, restoration, investigation, postmortem, and CAPA | `[ ]` |
| `docs/operations/support-sla.md` | Should | Service manager | Acknowledgement, workaround, restore, resolution, escalation, operating hours, monitoring, and reporting | `[ ]` |
| `docs/operations/training-and-competency.md` | Should | HR/quality | Role-based training, competency assessment, authorization, retraining, expiry, and task restrictions | `[ ]` |
| `docs/operations/kpi-dictionary.md` | Should | Data/operations | Metric definition, formula, source, period, owner, target, exclusions, privacy, and reconciliation | `[ ]` |
| `docs/operations/vendor-exit-and-handover.md` | Should | Procurement/ICT | Source ownership, documentation, exports, credentials, environments, local capacity, handover, and exit test | `[ ]` |

---

## 6. Documents for engagement, content, and optimization

| File | Priority | Owner | Purpose | Status |
|---|---|---|---|---|
| `docs/operations/donor-engagement.md` | Should | Donor services | Recruitment, access, appointment experience, aftercare, deferral counselling, segmentation, consent, recognition, and retention KPIs | `[ ]` |
| `docs/operations/public-content-governance.md` | Could | Communications/DPO | Content ownership, bilingual workflow, approval, statistics, source periods, privacy, corrections, and archive | `[ ]` |
| `docs/technical/analytics-and-forecasting.md` | Could | Data lead | De-identified warehouse, demand forecasting, stock balancing, model governance, validation, and limitations | `[ ]` |
| `docs/operations/glossary.md` | Should | Documentation owner | Approved English/Kiswahili terminology, acronyms, product names, state codes, and role names | `[ ]` |

---

## 7. ADRs to create before schema design

Create these Architecture Decision Records before implementing the affected database structure.

| ADR | Decision required | Priority |
|---|---|---|
| `ADR-001-donation-identifier.md` | National unique donation identifier ownership, generation, format, and collision handling | Must |
| `ADR-002-barcode-standard.md` | ISBT 128 or approved national equivalent, label layout, scanner requirements, and migration | Must |
| `ADR-003-component-domain-model.md` | Relationship between donation, collection container, samples, and zero-or-more components | Must |
| `ADR-004-quarantine-release-authority.md` | State model, complete-test rule, verifier/releaser duties, dual authorization, and emergency exceptions | Must |
| `ADR-005-hospital-integration-boundary.md` | Data held by NBTS versus hospital/HMIS; patient references, requests, receipt, and transfusion outcomes | Must |
| `ADR-006-offline-authority.md` | What can be captured offline, identifier generation, conflicts, server revalidation, and prohibited actions | Must |
| `ADR-007-audit-integrity.md` | Append-only evidence, hash verification, retention, exports, and privileged access | Should |
| `ADR-008-event-outbox.md` | Transactional events, notifications, retries, idempotency, and reconciliation | Should |
| `ADR-009-hosting-and-dr.md` | Hosting topology, environments, backup location, recovery site, and operational ownership | Should |
| `ADR-010-interoperability-profile.md` | Local FHIR/terminology profiles and integration engine strategy | Should |

---

## 8. Creation order

### Wave 1 — Documentation control

1. `README.md`
2. `docs/governance/documentation-governance.md`
3. `docs/governance/documentation-register.md`
4. `docs/governance/documentation-templates.md`

### Wave 2 — Discovery and approved design

5. `docs/overview/system-overview.md`
6. `docs/operations/center-operating-model.md`
7. `docs/security/roles-and-permissions.md`
8. `docs/security/clinical-safety.md`
9. `docs/security/risk-register.md`
10. `docs/technical/data-dictionary.md`
11. `docs/technical/domain-model.md`
12. Required ADRs

### Wave 3 — Core safety pilot specification

13. `docs/operations/laboratory-and-release.md`
14. `docs/operations/components-inventory-cold-chain.md`
15. `docs/operations/hospital-and-transfusion.md`
16. `docs/operations/haemovigilance-recall-capa.md`
17. `docs/operations/testing-and-validation.md`
18. `docs/security/security-and-privacy.md`
19. Updated `docs/technical/api.md`
20. Updated `docs/operations/runbook.md`

### Wave 4 — Scale and operations

21. `docs/operations/quality-management.md`
22. `docs/technical/interoperability.md`
23. `docs/operations/offline-and-downtime.md`
24. `docs/operations/disaster-recovery.md`
25. `docs/operations/incident-response.md`
26. `docs/operations/support-sla.md`
27. `docs/operations/training-and-competency.md`
28. `docs/operations/kpi-dictionary.md`
29. `docs/operations/rollout-and-acceptance.md`
30. `docs/operations/vendor-exit-and-handover.md`

### Wave 5 — Optimization

31. `docs/operations/donor-engagement.md`
32. `docs/operations/public-content-governance.md`
33. `docs/technical/analytics-and-forecasting.md`
34. `docs/operations/glossary.md`

---

## 9. Minimum document set before coding the clinical extension

Do not begin the laboratory/component/hospital schema implementation until these are approved or explicitly accepted as controlled pilot drafts:

- [~] `docs/overview/system-overview.md` exists in Review; required approvals remain pending.
- [~] `docs/operations/center-operating-model.md` exists in Review; required approvals remain pending.
- [~] `docs/security/roles-and-permissions.md` exists in Review; required approvals remain pending.
- [ ] `docs/security/clinical-safety.md`
- [ ] `docs/security/risk-register.md`
- [ ] `docs/technical/domain-model.md`
- [ ] `docs/technical/data-dictionary.md`
- [ ] `docs/operations/laboratory-and-release.md`
- [ ] `docs/operations/components-inventory-cold-chain.md`
- [ ] `docs/operations/hospital-and-transfusion.md`
- [ ] `docs/operations/testing-and-validation.md`
- [ ] `docs/security/security-and-privacy.md`
- [ ] Required ADRs
- [ ] Updated `docs/planning/task.md`
- [ ] Updated `docs/planning/workflow.md`

This gate exists because database and UI implementation before identifier, component, laboratory, release, hospital, and safety decisions are approved will create expensive rework and may encode unsafe assumptions.

---

## 10. Definition of documentation completion

A document is complete only when:

- [ ] Its owner is named.
- [ ] Its status is accurate.
- [ ] Scope and exclusions are clear.
- [ ] Related requirement IDs are linked.
- [ ] Current foundation and target behavior are separated.
- [ ] Assumptions and unresolved decisions are visible.
- [ ] Required roles and approvals are identified.
- [ ] Safety, privacy, audit, failure, downtime, and recovery are covered where relevant.
- [ ] Terms and codes match the data dictionary.
- [ ] Links to related files work.
- [ ] Required reviewers have reviewed it.
- [ ] Required approvers have approved the relevant version.
- [ ] The register reflects its current status.
