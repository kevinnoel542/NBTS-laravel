# Local demo credentials

Last verified: 2026-08-13

These accounts are created only by `DemoDataSeeder` in local and testing environments. They all use the local demo password `Password123!`.

| Role | Email | Staff web access | Primary dashboard |
| --- | --- | --- | --- |
| Super administrator | `admin@nbts.test` | Yes | System control |
| NBTS administrator | `nbts-admin@nbts.test` | Yes | National operations |
| Center manager | `manager@nbts.test` | Yes | Center management |
| Center staff | `staff@nbts.test` | Yes | Reception; includes two scoped center assignments for context-switch testing |
| Donor | `donor@nbts.test` | No; use the donor/mobile API flow | Donor home remains a mobile/API contract |

All five accounts are seeded and permission tested. The Phase 5 account-boundary QA covers all five accounts, and the Phase 6 donor-journey QA uses the center-manager account. Existing local account passwords are preserved by the seeder; recreate the local database only when an intentional credential reset is required.

Email verification is disabled while the system is under construction. Re-enable it only after the pilot security decision recorded in `docs/workflow.md`.
