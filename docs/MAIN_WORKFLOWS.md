# Main Workflows

This file explains the main things the project does, step by step.

## Donor Registration

1. A donor sends registration details to the API.
2. The system validates name, email, phone, password, blood group, gender, region, and date of birth.
3. The system creates the user.
4. The system assigns the donor role.
5. The system creates a donor profile.
6. The system creates a donor ID.
7. The system returns a login token.

## Donor Login

1. A donor sends email or phone and password.
2. The system finds the user by email or phone.
3. The system checks the password.
4. The system returns a login token.

## Donor Finds A Blood Center

1. The donor opens the public website or mobile app.
2. The system lists active blood centers.
3. The donor can view details for one center.
4. The donor books through the mobile app.

## Donor Books An Appointment

1. The donor must be logged in.
2. The donor chooses a blood center and time.
3. The API creates an appointment.
4. The appointment status starts as pending.
5. Staff can later confirm or cancel it.

## Staff Confirms An Appointment

1. Staff must be logged in.
2. Staff must have `appointments.manage`.
3. Staff confirms a pending appointment.
4. The system changes status to confirmed.
5. The system stores who handled it.

## Donor Cancels An Appointment

1. The donor must be logged in.
2. The appointment must belong to the donor.
3. The appointment must not already be completed or cancelled.
4. The system changes the status to cancelled.
5. The system stores the cancelled time.

## Staff Checks Eligibility

1. Staff must be logged in.
2. Staff searches for a donor.
3. Staff submits screening data.
4. The system checks donor rules.
5. The system stores an eligibility record.
6. The system updates the donor profile.

The system checks:

- Permanent deferral.
- Temporary deferral.
- Age under 18.
- Weight under 50 kg.
- Next eligible donation date.

## Staff Defers A Donor

1. Staff must have `donors.manage`.
2. Staff chooses temporary or permanent deferral.
3. Staff adds a reason.
4. The system creates an active deferral.
5. The donor profile eligibility status is updated.

## Staff Lifts A Deferral

1. Staff must have `donors.manage`.
2. Staff chooses a deferral.
3. The system marks it inactive.
4. The system stores who lifted it.
5. The system runs a new eligibility check.

## Staff Records A Donation

1. Staff must have `donations.record`.
2. Staff selects a donor.
3. The donor must have the donor role.
4. Staff enters donation details.
5. If the donation is appointment based, an appointment ID is required.
6. If the donation is completed, the system checks donor eligibility.
7. The system creates the donation record.
8. If there is an appointment, the appointment is marked completed.
9. The donor blood group and last donation date are updated.
10. The donor profile is updated.
11. The donor next eligible donation date is set to 90 days after donation.
12. Badges and rewards are awarded if the donor qualifies.
13. A blood unit is created.
14. Inventory is increased.
15. Low stock status is checked.

## Staff Verifies Blood Group

1. Staff must have `donations.record`.
2. Staff chooses a donation.
3. Staff submits the confirmed blood group.
4. The donation is marked verified.
5. The donor user record is updated.
6. The donor profile is updated.

## Blood Unit Creation

1. A completed donation creates a blood unit.
2. The system creates a unique unit number.
3. The unit status starts as available.
4. The collection date comes from the donation date.
5. The expiry date is 35 days after collection.
6. Inventory increases by 1 available unit.

## Blood Unit Status Change

1. Staff must have `inventory.manage`.
2. Staff changes the blood unit status.
3. If the unit moves out of available status, available inventory decreases.
4. If the unit moves into available status, available inventory increases.
5. The system records an inventory adjustment.
6. The system checks low stock alerts.

## Manual Inventory Adjustment

1. Staff must have `inventory.manage`.
2. Staff chooses center, blood group, quantity change, reason, and notes.
3. The system checks that inventory will not go below zero.
4. The system updates available units.
5. The system records the adjustment.
6. The system checks low stock alerts.

## Expiring Old Blood Units

1. Staff runs the expire due action.
2. The system finds collected, testing, available, or reserved units past their expiry date.
3. The system changes them to expired.
4. Inventory is reduced when needed.

## Low Stock Alert

1. Inventory changes.
2. The system compares available units with the minimum threshold.
3. If available units are too low, the system creates or updates an open alert.
4. If stock is enough again, the system marks open alerts as resolved.

## Emergency Campaign

1. Staff sees a low stock alert.
2. Staff creates an emergency campaign.
3. The system creates a campaign for the alert blood group and center.
4. The alert status changes to campaign created.
5. If a campaign already exists for that alert, the system returns the existing campaign.

## Donor Loyalty

1. A completed donation updates the donor total donations.
2. The system checks active badges.
3. The system checks active rewards.
4. If the donor reached a threshold, the system awards the badge or reward.
5. The leaderboard is refreshed.

## Reports

Staff can view:

- Summary report.
- Donation report.
- Inventory report.

The summary report shows:

- Donor count.
- Completed donation count.
- Available blood units.
- Low stock groups.
- Active campaigns.

The donation report shows:

- Donations by blood group.
- Monthly completed donations.

The inventory report shows:

- Inventory by center and blood group.
- Blood unit counts by status.

