# NBTS documentation control center

Last updated: 2026-08-27

Owner: Documentation owner

Status: Active

## Purpose

This directory is the controlled documentation entry point for NBTS-NEW. It separates the verified current Laravel, web, mobile, and operational foundation from the target national blood-management service.

The target service extends from donor engagement through screening, collection, identification, laboratory testing, quarantine and release, component production, inventory, cold chain, hospital request and compatibility, transfusion outcome, haemovigilance, recall, quality management, resilience, and controlled national rollout.

Target workflow text is not proof that a feature exists or that a clinical rule has been approved.

## Authoritative documents

| Question | Document |
|---|---|
| What is planned, pending, blocked, or completed? | [planning/task.md](planning/task.md) |
| How should the target system operate? | [planning/workflow.md](planning/workflow.md) |
| What has been implemented and directly verified? | [evidence/achievement.md](evidence/achievement.md) |
| What API contract is currently supported? | [technical/api.md](technical/api.md) |
| How is the current system operated, backed up, and restored? | [operations/runbook.md](operations/runbook.md) |
| How are documentation status, evidence, and approvals controlled? | [governance/documentation-governance.md](governance/documentation-governance.md) |
| Which controlled documents exist or remain missing? | [governance/documentation-register.md](governance/documentation-register.md) |
| Which standard document structures should be used? | [governance/documentation-templates.md](governance/documentation-templates.md) |
| What are the current and target system boundaries? | [overview/system-overview.md](overview/system-overview.md) |
| How are centers, departments, locations, and assignments organized? | [operations/center-operating-model.md](operations/center-operating-model.md) |
| Which role profiles, permissions, scopes, dashboards, and duty separations apply? | [security/roles-and-permissions.md](security/roles-and-permissions.md) |

## Folder order

```text
docs/
├── README.md
├── planning/      # roadmap, phase checklist, target workflow
├── overview/      # system boundary and high-level product view
├── governance/    # document control, register, templates
├── technical/     # API and technical contracts
├── operations/    # operating model and runbooks
├── security/      # roles, permissions, local demo credentials
└── evidence/      # achievements, QA reports, screenshots
```

## Current execution road

The active road follows the dependency order defined in `docs/planning/task.md` and `docs/planning/workflow.md`:

1. Complete foundation-and-discovery evidence and approve the target operating model.
2. Define center hierarchy, departments, operational roles, assignments, visibility, and separation of duties.
3. Implement and validate donor reception, screening, collection, and the approved identifier/barcode chain.
4. Implement laboratory testing, QC, hard quarantine, and authorized release.
5. Implement component lineage, component-level inventory, cold chain, transfer, and delivery.
6. Implement hospital requests, compatibility, issue, receipt, transfusion outcomes, and returns.
7. Implement haemovigilance, recall/look-back, CAPA, quality, security, resilience, and change control.
8. Complete the workflow-driven staff UI, messaging, reports, PDFs, managed public content, and supported mobile/device journeys.
9. Exit through a controlled pilot, regional scale, national optimization, and formal acceptance.

Implementation does not begin with safety-critical schema assumptions. The minimum discovery documents and decisions listed in `docs/governance/documentation-register.md` must be approved or explicitly accepted as controlled pilot drafts first.

## Current documentation gate

Documentation control is active. The following Phase 5 operating-model artifacts are now in review and still require the approvals recorded in their headers:

- `docs/overview/system-overview.md`
- `docs/operations/center-operating-model.md`
- `docs/security/roles-and-permissions.md`

The Phase 6 Laravel construction foundation for donor reception, eligibility, collection, quarantine-only traceability, and controlled offline reconciliation is implemented and verified. The visible browser record is available in the [Phase 6 donor journey QA report](evidence/phase-6-donor-journey-qa/report.md). Production clinical rules, the national identifier/barcode standard, and field-device controls retain the external approval boundaries recorded in `docs/planning/task.md`.

The next safety/data artifacts remain:

- `docs/security/clinical-safety.md`
- `docs/operations/risk-register.md`
- `docs/technical/domain-model.md`
- `docs/technical/data-dictionary.md`
- Required architecture decision records under `docs/adr/`

Laboratory, component, cold-chain, and hospital specifications follow after the operating model and safety/data foundations are reviewed.

## Status and evidence rules

- `[ ]` means not started or not proven.
- `[-]` means in progress.
- `[x]` means implemented and verified with the required evidence.
- `[!]` means blocked by a named external decision, credential, service, or environment.
- Planning belongs in `docs/planning/task.md`.
- Target behavior belongs in `docs/planning/workflow.md`.
- Only completed and verified outcomes belong in `docs/evidence/achievement.md`.
- Clinical, laboratory, safety, privacy, identifier, retention, release, and recovery rules require the approvals identified by documentation governance.

## Security and privacy

Do not place production passwords, private keys, access tokens, Firebase service-account content, production personal data, patient identifiers, or unnecessary donor health data in controlled documentation. The construction-only shared password for the five local compatibility accounts may be recorded only in `docs/security/local-demo-credentials.md`; it must never be reused outside local or test environments.
