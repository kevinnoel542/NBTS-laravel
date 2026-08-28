# Local demo credentials

Last verified: 2026-08-28

These accounts are created only by `DemoDataSeeder` in local and testing environments. They are safe for local browser QA and demos only. Do not use or copy them into production.

Shared local demo password for every seeded demo account: `Password123!`

| Account | Email | Password | Access | Primary dashboard / purpose |
| --- | --- | --- | --- | --- |
| Super administrator | `admin@nbts.test` | `Password123!` | Staff web login | System control, administration, intelligence, rollout oversight |
| NBTS administrator | `nbts-admin@nbts.test` | `Password123!` | Staff web login | National operations, national center selector, rollout command |
| Center manager | `manager@nbts.test` | `Password123!` | Staff web login | Center operations, donor flow, inventory signals, local rollout visibility |
| Center staff | `staff@nbts.test` | `Password123!` | Staff web login | Reception desk, donor reception, appointments, scoped center work |
| Donor | `donor@nbts.test` | `Password123!` | Donor/mobile API contract | Donor profile and mobile/API testing; not a staff dashboard account |

Staff web login URL for local QA: `http://127.0.0.1:8003/login`

Expected account behavior:

- `admin@nbts.test`, `nbts-admin@nbts.test`, `manager@nbts.test`, and `staff@nbts.test` can use the staff web console.
- `donor@nbts.test` is seeded as a donor account and must not receive staff dashboard access.
- Email verification is disabled while the system is under construction.
- Existing local account passwords are preserved by the seeder; recreate the local database only when an intentional credential reset is required.

Verification evidence:

- Phase 5 account-boundary QA covers the five compatibility accounts.
- Phase 6 donor-journey QA uses the center-manager and staff accounts.
- Phase 13 rollout browser QA uses `admin@nbts.test` and `nbts-admin@nbts.test`.
