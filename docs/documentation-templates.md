# NBTS Documentation Templates

Last updated: 2026-08-11  
Document ID: `DOC-TPL-001`  
Status: Active template library

## 1. Controlled document header

```md
# Document title

Last updated: YYYY-MM-DD
Document ID: AREA-NAME-NNN
Owner:
Status: Draft | Review | Approved | Active | Superseded | Archived
Approval:
Related requirements:
Related ADRs:
Related SOPs/policies:
```

---

## 2. Roadmap requirement template

```md
### REQUIREMENT-ID — Requirement title

Status: [ ]
Priority: Must | Should | Could
Safety: Critical | High | Standard
Owner:
Approvers:
Dependencies:
Affected centers:
Affected departments:
Affected roles:
Affected documents:

Problem:
- What risk, delay, error, or service need does this solve?

Required behavior:
- ...

Prohibited behavior:
- ...

Implementation:
- [ ]

Authorization:
- [ ]

Audit:
- [ ]

Failure and exception handling:
- [ ]

Offline/downtime:
- [ ]

Verification:
- [ ] Domain tests
- [ ] Authorization tests
- [ ] Negative/bypass tests
- [ ] API/UI tests
- [ ] Reconciliation/traceability tests
- [ ] Browser/device checks
- [ ] Operational validation

Completion gate:
- [ ]

Known constraints:
- ...
```

---

## 3. Achievement entry template

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

Traceability/reconciliation evidence:
- ...

Operational drill/validation:
- ...

Approvals:
- ...

Known limitations:
- ...

Rollback/recovery:
- ...

Next dependent task:
- ...
```

---

## 4. Workflow template

```md
## Workflow name

Workflow ID:
Owner:
Trigger:
Scope:
Actors:
Related requirements:
Related policies/SOPs:

### Preconditions

- ...

### Main flow

1. ...
2. ...
3. ...

### State transitions

`state_a → state_b → state_c`

### Authorization

- ...

### Separation of duties

- ...

### Audit events

- ...

### Notifications

- ...

### Failure and exception paths

- ...

### Offline/downtime behavior

- ...

### Final accounted outcomes

- ...

### Safety invariants

- The system must never ...
- The system must block ...

### Verification

- ...
```

---

## 5. Risk-register entry template

```md
### RISK-ID — Hazard title

Area:
Owner:
Affected workflow:
Likelihood: Low | Medium | High
Impact: Standard | High | Critical
Initial risk:
Existing controls:
Required controls:
Early-warning indicators:
Detection method:
Response:
Residual risk:
Acceptance authority:
Evidence:
Review date:
Status:
```

---

## 6. ADR template

```md
# ADR-NNN — Decision title

Date:
Status: Proposed | Accepted | Rejected | Superseded | Deprecated
Owners:
Decision authority:
Related requirements:
Supersedes:
Superseded by:

## Context

What problem or irreversible choice requires a decision?

## Options considered

### Option A

Benefits:
- ...

Risks:
- ...

### Option B

Benefits:
- ...

Risks:
- ...

## Decision

- ...

## Safety and privacy consequences

- ...

## Data and migration consequences

- ...

## API and integration consequences

- ...

## Operational consequences

- ...

## Validation required

- ...

## Rollback or exit path

- ...

## Approval

- ...
```

---

## 7. Data-dictionary entry template

```md
### FIELD-ID — Field or code name

Entity:
Technical name:
Display name:
Type:
Required:
Allowed values:
Default:
Source:
Owner:
Validation:
Meaning:
Sensitive classification:
Allowed roles:
Audit requirement:
Retention:
Correction rule:
API mapping:
Legacy mapping:
Notes:
```

---

## 8. API endpoint template

```md
## METHOD /api/v1/path

Capability:
Requirement IDs:
Authentication:
Abilities/permissions:
Center/hospital scope:
Idempotency:
Rate limit:

### Request

Headers:
```json
{}
```

Body:
```json
{}
```

### Success response

Status:
```json
{}
```

### Errors

| Code | HTTP | Meaning |
|---|---:|---|

### Audit

- ...

### Events/outbox

- ...

### Compatibility

- Added in:
- Deprecated in:
- Removal:
- Flutter impact:

### Tests

- ...
```

---

## 9. Test-case template

```md
### TEST-ID — Test title

Requirement:
Risk:
Type: Unit | Domain | Feature | Authorization | Integration | UI | Device | Operational drill
Environment:
Preconditions:
Test data:

Steps:
1. ...

Expected result:
- ...

Evidence:
- Command:
- Result:
- Screenshot/log/reference:

Status:
Owner:
Date:
```

---

## 10. Change-control template

```md
# CHANGE-ID — Change title

Date:
Requester:
Owner:
Class: Clinical | Laboratory | Privacy | Data | Operational | Infrastructure | Emergency
Priority:
Related requirements:
Related ADRs:

## Reason

- ...

## Current behavior

- ...

## Proposed behavior

- ...

## Risk assessment

- ...

## Data/migration impact

- ...

## API/mobile impact

- ...

## Security/privacy impact

- ...

## Test and validation plan

- ...

## Rollback plan

- ...

## Downtime/continuity plan

- ...

## Training and communication

- ...

## Documentation to update

- [ ]

## Approvals

- [ ]

## Release and post-release review

- ...
```

---

## 11. Approval record template

```md
## Approval record

Document/change:
Version:
Scope approved:
Approver name:
Role:
Organization:
Decision: Approved | Approved with conditions | Rejected
Conditions:
Date:
Signature/reference:
Next review:
```

---

## 12. KPI definition template

```md
### KPI-ID — KPI name

Purpose:
Owner:
Audience:
Formula:
Numerator:
Denominator:
Unit:
Source tables/events:
Filters:
Exclusions:
Reporting period:
Refresh frequency:
Target/threshold:
Privacy classification:
Reconciliation method:
Known limitations:
Approval:
```

---

## 13. Incident record template

```md
# INCIDENT-ID — Incident title

Start time:
Detected time:
Resolved time:
Severity:
Affected services/centers:
Incident commander:
Clinical safety notified:
Quality notified:
DPO/security notified:

## Impact

- ...

## Immediate containment

- ...

## Downtime/workaround

- ...

## Restoration

- ...

## Data and traceability verification

- ...

## Root cause

- ...

## CAPA

- ...

## Communication

- ...

## Evidence

- ...

## Follow-up requirements

- [ ]
```
