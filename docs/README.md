# NBTS documentation control center

Last updated: 2026-08-11

Owner: Documentation owner

Status: Active

## Purpose

This directory is the controlled documentation entry point for NBTS-NEW. It separates the verified current Laravel, web, mobile, and operational foundation from the target national blood-management service.

The target service extends from donor engagement through screening, collection, identification, laboratory testing, quarantine and release, component production, inventory, cold chain, hospital request and compatibility, transfusion outcome, haemovigilance, recall, quality management, resilience, and controlled national rollout.

Target workflow text is not proof that a feature exists or that a clinical rule has been approved.

## Authoritative documents

| Question | Document |
|---|---|
| What is planned, pending, blocked, or completed? | [task.md](task.md) |
| How should the target system operate? | [workflow.md](workflow.md) |
| What has been implemented and directly verified? | [achievement.md](achievement.md) |
| What API contract is currently supported? | [api.md](api.md) |
| How is the current system operated, backed up, and restored? | [operations.md](operations.md) |
| How are documentation status, evidence, and approvals controlled? | [documentation-governance.md](documentation-governance.md) |
| Which controlled documents exist or remain missing? | [documentation-register.md](documentation-register.md) |
| Which standard document structures should be used? | [documentation-templates.md](documentation-templates.md) |
| How do the backend and UI/documentation agents coordinate delivery? | [ui-documentation-workstream.md](ui-documentation-workstream.md) |

## Current execution road

The active road follows the dependency order defined in `task.md` and `workflow.md`:

1. Complete foundation-and-discovery evidence and approve the target operating model.
2. Define center hierarchy, departments, operational roles, assignments, visibility, and separation of duties.
3. Implement and validate donor reception, screening, collection, and the approved identifier/barcode chain.
4. Implement laboratory testing, QC, hard quarantine, and authorized release.
5. Implement component lineage, component-level inventory, cold chain, transfer, and delivery.
6. Implement hospital requests, compatibility, issue, receipt, transfusion outcomes, and returns.
7. Implement haemovigilance, recall/look-back, CAPA, quality, security, resilience, and change control.
8. Complete the workflow-driven staff UI, messaging, reports, PDFs, managed public content, and supported mobile/device journeys.
9. Exit through a controlled pilot, regional scale, national optimization, and formal acceptance.

Implementation does not begin with safety-critical schema assumptions. The minimum discovery documents and decisions listed in `documentation-register.md` must be approved or explicitly accepted as controlled pilot drafts first.

## Current documentation gate

Documentation control is active. The next required artifacts are:

- `system-overview.md`
- `center-operating-model.md`
- `roles-and-permissions.md`
- `clinical-safety.md`
- `risk-register.md`
- `domain-model.md`
- `data-dictionary.md`
- Required architecture decision records under `docs/adr/`

Laboratory, component, cold-chain, and hospital specifications follow after the operating model and safety/data foundations are reviewed.

## Status and evidence rules

- `[ ]` means not started or not proven.
- `[-]` means in progress.
- `[x]` means implemented and verified with the required evidence.
- `[!]` means blocked by a named external decision, credential, service, or environment.
- Planning belongs in `task.md`.
- Target behavior belongs in `workflow.md`.
- Only completed and verified outcomes belong in `achievement.md`.
- Clinical, laboratory, safety, privacy, identifier, retention, release, and recovery rules require the approvals identified by documentation governance.

## Security and privacy

Do not place passwords, private keys, access tokens, Firebase service-account content, production personal data, patient identifiers, or unnecessary donor health data in controlled documentation.
