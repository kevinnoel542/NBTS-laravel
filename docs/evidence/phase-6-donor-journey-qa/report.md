# Phase 6 donor journey browser QA

| Field | Value |
| --- | --- |
| Date | 2026-08-13 |
| App URL | `http://127.0.0.1:8003` |
| Session | `nbts-live` |
| Viewport | 1600×900 |
| Role and scope | Center manager at Muhimbili Blood Center |
| Scope | Donor reception, eligibility, collection traceability, quarantine, offline controls, navigation, theme, overflow, and browser errors |

## Summary

Visible browser QA is complete. The full donor-to-quarantine journey and the controlled offline surfaces passed at 1600×900. All findings discovered during this run were corrected and rechecked in the same headed Chromium session.

| Severity | Count |
| --- | ---: |
| Critical | 0 |
| High | 0 |
| Medium | 1 |
| Low | 2 |
| Total findings | 3 |
| Resolved | 3 |
| Open | 0 |

## Verified journey

| Step | Result | Evidence |
| --- | --- | --- |
| Donor reception | Passed; compact scoped search, status filters, registration, identity, and duplicate actions render without empty card gaps | [Reception](screenshots/01-donor-reception.png) |
| Eligibility and counselling | Passed; checked-in queue, concise page explanation, screening actions, filters, and pagination controls fit the viewport | [Eligibility](screenshots/02-eligibility.png) |
| Ready-for-collection queue | Passed; an eligible, identity-confirmed donor appears once in the correct center queue | [Ready queue](screenshots/03-collection-ready-queue.png) |
| Collection preparation | Passed; the centered dialog stays within the viewport and captures bag configuration, lot, volume, and device | [Preparation dialog](screenshots/04-prepare-collection-modal.png) |
| Label issue and application | Passed; controlled labels are issued and the Code 128 view renders with no-store response headers | [Issued labels](screenshots/05-issued-labels.png), [barcode](screenshots/06-code128-label.png) |
| Specimen traceability | Passed; required specimens are scan-collected and handed off against the same donation identifier | [Specimens](screenshots/07-specimen-traceability.png) |
| In-progress trace | Passed; both collected and handed-off specimens count as completed traceability work | [In-progress](screenshots/08-in-progress-complete-trace.png) |
| Collection completion | Passed; completion remains a bounded centered dialog and requires aftercare and donor acknowledgement | [Completion dialog](screenshots/09-complete-collection.png) |
| Quarantine history | Passed; successful completion creates quarantine-only compatibility stock and exposes no release action | [Quarantine](screenshots/10-quarantine-history.png) |
| Offline device and identifier batch | Passed; assigned device registration, one-time credential handling, active batch issue, and revocation controls are visible | [Offline controls](screenshots/11-offline-device-and-batch.png) |
| Controlled downtime form | Passed; the numbered form renders at 1600×900 with `no-store` caching | [Downtime form](screenshots/12-controlled-downtime-form.png) |
| Navigation and theme | Passed; dark theme, compact page header, and animated 60-pixel icon rail have no horizontal overflow | [Collapsed navigation](screenshots/13-collapsed-dark-navigation.png) |

The browser journey produced collection identifier `TZC000420260000001O`, completed it into quarantine, and left the headed browser open for review. Current page-error inspection returned no errors; the console contained only the development logger.

## Issues and resolutions

### ISSUE-001 — Unsupported icon names leave action icons blank

| Field | Value |
| --- | --- |
| Severity | Low |
| Category | Visual |
| Status | Resolved |

Unsupported icon names in the Phase 6 workspace were replaced with icons available in the installed Flux/Heroicon set. The final availability check found no missing icons.

### ISSUE-002 — Short actions open as oversized side panels

| Field | Value |
| --- | --- |
| Severity | Low |
| Category | Interaction / layout |
| Status | Resolved |

Short identity, duplicate, collection-preparation, label-replacement, and offline-management actions now use content-sized centered dialogs. The longer registration and screening workflows remain flyouts. Completion and reaction dialogs are vertically bounded and scroll only when their content exceeds the viewport.

### ISSUE-003 — Handed-off specimens are omitted from progress totals

| Field | Value |
| --- | --- |
| Severity | Medium |
| Category | Functional / data presentation |
| Status | Resolved |

The in-progress worklist originally counted only specimens in `collected` state. It now counts both `collected` and `handed_off`, and a regression test proves the two states are represented correctly.

## Production acceptance boundaries

The Laravel construction foundation is complete, but the following external approvals are intentionally not claimed:

- National donor-identifier source, legal basis, matching, access, and retention rules.
- Approved production screening questionnaire, thresholds, referral rules, and effective date.
- ISBT 128 or approved national-equivalent identifier, barcode, label, scanner, and printer validation.
- Approved offline dataset, device baseline, key management, retention, loss/wipe procedure, and field-client implementation.

These boundaries do not reopen the verified Phase 6 Laravel work. They control promotion from construction mode to an approved production clinical workflow.
