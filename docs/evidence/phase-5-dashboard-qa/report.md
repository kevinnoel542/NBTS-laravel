# Phase 5 dashboard browser QA

| Field | Value |
| --- | --- |
| Date | 2026-08-12 |
| App URL | `http://127.0.0.1:8003` |
| Session | `nbts-live` |
| Viewport | 1600×900 |
| Scope | Five compatibility accounts, representative role-aware dashboards, navigation collapse, theme, assignment scope, console and page errors |

## Summary

Browser QA is complete. All findings were fixed and visibly rechecked in the same headed Chromium session.

| Severity | Count |
| --- | ---: |
| Critical | 0 |
| High | 0 |
| Medium | 2 |
| Low | 2 |
| Total findings | 4 |
| Resolved | 4 |
| Total open | 0 |

## Verified views

| Account / state | Result | Evidence |
| --- | --- | --- |
| Super administrator — system control | Passed; real scoped metrics, priority queue, quick actions, supported icons | [1600×900 screenshot](screenshots/issue-001-resolved.png) |
| NBTS administrator — national operations | Passed; national summary and permitted navigation only | [1600×900 screenshot](screenshots/nbts-admin-1600x900.png) |
| Center manager — center management | Passed in dark and light themes with no horizontal overflow | [Light-theme 1600×900 screenshot](screenshots/center-manager-light-1600x900.png) |
| Center staff — reception | Passed; compact assignment labels and scoped operational metrics | [1600×900 screenshot](screenshots/center-staff-1600x900.png) |
| Center staff — assignment switch | Passed; switching to Eastern Zone changed active scope and scoped data without permission leakage | [1600×900 screenshot](screenshots/assignment-switch-eastern-zone-1600x900.png) |
| Donor boundary | Passed; donor is rejected by the staff-only web login and remains mobile/API-only | [1600×900 screenshot](screenshots/donor-boundary-1600x900.png) |
| Collapsed navigation | Passed; animated icon-only rail measured 60 pixels at 1600×900 | [1600×900 screenshot](screenshots/navigation-collapsed-1600x900.png) |

The tested dashboard pages reported no page errors or current JavaScript exceptions. Both themes remained within the 1600-pixel viewport.

## Issues and resolutions

### ISSUE-001 — Two unsupported metric icon names render empty

| Field | Value |
| --- | --- |
| Severity | Low |
| Category | Visual |
| URL | `/dashboard` as `admin@nbts.test` |
| Evidence | [Annotated 1600×900 screenshot](screenshots/issue-001-missing-metric-icons.png) |
| Resolution evidence | [Corrected 1600×900 screenshot](screenshots/issue-001-resolved.png) |
| Status | Resolved |

The Active accounts and Organization units metric tiles originally used unsupported names. Their configuration now uses icons supported by the existing application icon set, and both render visibly.

### ISSUE-002 — Active assignment label clips the organization name

| Field | Value |
| --- | --- |
| Severity | Low |
| Category | Content / visual |
| URL | `/dashboard` as `admin@nbts.test` |
| Evidence | [Annotated 1600×900 screenshot](screenshots/issue-002-clipped-assignment-label.png) |
| Resolution evidence | [Corrected national-label screenshot](screenshots/issue-002-resolved.png) |
| Status | Resolved |

The header and sidebar originally showed the full organization name inside compact context controls. Active-context labels now use concise organization short names such as `NBTS TZ`, `Muhimbili`, and `Eastern Zone`.

### ISSUE-003 — Different center assignments have indistinguishable labels

| Field | Value |
| --- | --- |
| Severity | Medium |
| Category | Functional / UX |
| URL | `/dashboard` as `staff@nbts.test` |
| Evidence | [Annotated 1600×900 screenshot](screenshots/issue-003-duplicate-assignment-options.png) |
| Resolution evidence | [Corrected selector screenshot](screenshots/issue-003-resolved.png) |
| Status | Resolved |

The selector originally showed identical city-based labels for separate Muhimbili and Eastern Zone assignments. Seeded organization short names are now compact and unique, so an operator can safely select the intended responsibility.

### ISSUE-004 — National inventory snapshot repeats blood-group labels by center

| Field | Value |
| --- | --- |
| Severity | Medium |
| Category | Functional / data presentation |
| URL | `/dashboard` as `admin@nbts.test` in National view |
| Evidence | [Pre-fix screenshot](screenshots/issue-002-resolved.png) |
| Resolution evidence | [Aggregated 1600×900 screenshot](screenshots/issue-004-resolved.png) |
| Status | Resolved |

The final national view exposed one inventory cell per center and therefore repeated blood-group labels. The snapshot query now groups by blood group and sums available, reserved, and threshold values across visible centers. A regression test requires two center rows for `O+` to render as one national total; the visible result now contains exactly the eight unique blood groups.
