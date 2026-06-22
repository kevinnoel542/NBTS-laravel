Blood Donation Platform — System Requirements
1. Project Vision

This project is a National Blood Donation and Blood Bank Management Platform.

The platform supports:

donor registration
mobile donor app
admin/staff dashboard
appointment booking
walk-in donations
blood center operations
donor eligibility tracking
digital donor card
QR verification
blood inventory
blood unit lifecycle
loyalty program
notifications
emergency blood campaigns

The system is inspired by the NBTS partnership goal of improving voluntary blood donation, digital donor engagement, donor retention, internal systems, and data-driven campaigns.

2. Main System Parts
Public Website

Purpose: information only.

Features:

landing page
about blood donation
campaigns
blood centers
download mobile app
contact page

No donor login on website.

Flutter Mobile App

Purpose: donor-facing app.

Features:

donor registration
donor login
digital donor card
QR code
donor ID
appointment booking
donation history
badges
rewards
notifications
Laravel Backend

Purpose: central system.

Includes:

API for Flutter
database
authentication
business logic
notification engine
eligibility engine
inventory engine
Filament Admin Panel

Purpose: admin, center manager, and staff operations.

Features:

manage donors
manage staff
manage blood centers
manage appointments
record donations
verify blood group
manage blood units
manage inventory
manage campaigns
view reports
3. User Roles
Super Admin

Can manage everything.

NBTS Admin

Can manage national-level data, campaigns, reports, and centers.

Center Manager

Can manage only their assigned blood center.

Center Staff

Can record donations, search donors, scan QR codes, and update donor blood group verification.

Donor

Uses the Flutter app only.

4. Key Decisions
Donors use the mobile app only.
Website is public information only.
Admin/staff record completed donations.
Donors can self-register from app.
Admin can also manually create donors.
Donors can book appointments.
Walk-in donations are allowed.
Donation completion updates donor stats.
System calculates next eligible donation date.
System awards badges and rewards.
Blood centers have managers and staff accounts.
Loyalty system includes badges, recognition, leaderboards, and rewards.
Notifications include push, SMS, and email.
Donor card includes QR code and donor ID.
Staff can scan QR, search donor ID, phone, or name.
Blood inventory is tracked per center.
Low stock triggers alerts and emergency campaigns.
Eligibility engine tracks health, age, weight, gender, deferrals, and donation dates.
Inventory updates automatically after donation but staff can adjust later.
Staff can verify or update donor blood group.
Blood units are tracked individually and also counted in inventory totals.
Expired blood units are automatically marked expired, inventory reduced, alert generated, and staff confirms disposal.
5. Core Database Entities

Required tables/modules:

users
roles
permissions
donor_profiles
blood_centers
center_staff
appointments
donations
blood_units
blood_inventory
inventory_adjustments
campaigns
low_stock_alerts
notifications
badges
donor_badges
rewards
donor_rewards
leaderboards
eligibility_records
deferrals
audit_logs
6. Donor Workflow

Donor journey:

Download mobile app
Register account
Login
Receive digital donor card
Book appointment or walk in
Visit blood center
Staff scans QR or searches donor ID
Staff verifies donor eligibility
Staff records donation
Blood group is verified or updated
Donation history is updated
Blood unit is created
Inventory is updated
Badge/reward is calculated
Donor receives notification
7. Staff Donation Recording Workflow

Staff can find donor by:

QR code
donor ID
phone number
name

Then staff can:

verify donor profile
verify blood group
check eligibility
record donation
create blood unit
update inventory
trigger badge/reward update
8. Blood Group Verification

Donor blood group can be:

unknown
user selected
staff verified

Fields needed:

blood_group
blood_group_verified
blood_group_verified_at
blood_group_verified_by

Staff must be able to update blood group after lab confirmation.

9. Blood Unit Lifecycle

When donation is recorded, the system creates a blood unit.

Blood unit statuses:

collected
testing
available
reserved
transferred
used
rejected
expired
discarded

Each unit should have:

unit_number
donation_id
donor_id
blood_center_id
blood_group
collection_date
expiry_date
status
current_location
10. Inventory Rules

Inventory is tracked by:

blood center
blood group
available units
minimum threshold

When donation is recorded:

blood unit is created
inventory increases

When unit is used, transferred, expired, or discarded:

inventory decreases
audit log is saved
11. Low Stock Alert Workflow

When stock drops below threshold:

Create low stock alert
Notify center manager
Notify NBTS admin
Create emergency campaign
Find eligible donors nearby
Send push notification
Send SMS
Send email
12. Notification Types

Notification triggers:

appointment created
appointment confirmed
appointment reminder
appointment cancelled
donation recorded
donor eligible again
badge earned
reward unlocked
low blood stock
emergency campaign
campaign near donor

Channels:

push notification
SMS
email
13. Development Phases
Phase 1 — Core Foundation

Build:

roles and permissions
users
donor profiles
blood centers
center staff
authentication
Filament access control

Goal:

Create the main system structure.

Phase 2 — Donor API and Mobile Support

Build APIs for Flutter:

register
login
logout
profile
donor card
blood centers
campaigns

Goal:

Allow mobile app to connect and authenticate.

Phase 3 — Appointment and Walk-In System

Build:

appointment booking
appointment status management
walk-in donation support
center appointment view

Goal:

Allow donors to book or walk in.

Phase 4 — Donation Recording System

Build:

donor search
QR lookup
donor ID lookup
phone/name lookup
donation recording
donation history
blood group verification

Goal:

Allow staff to record real donations correctly.

Phase 5 — Eligibility Engine

Build:

eligibility status
next eligible donation date
temporary deferrals
permanent deferrals
age/weight/health checks

Goal:

Prevent notifications and donations for ineligible donors.

Phase 6 — Digital Donor Card

Build:

donor ID generator
QR code generator
donor card API
mobile donor card data

Goal:

Allow fast donor verification.

Phase 7 — Loyalty Program

Build:

badges
donor levels
milestone tracking
rewards
donor reward history
leaderboards

Goal:

Increase donor retention and motivation.

Phase 8 — Notification Engine

Build:

push notification preparation
SMS system
email system
notification templates
notification history

Goal:

Keep donors engaged and informed.

Phase 9 — Blood Unit and Inventory Management

Build:

blood unit creation
unit lifecycle tracking
inventory totals
manual inventory adjustments
expiry handling
disposal confirmation

Goal:

Track real blood supply per center.

Phase 10 — Low Stock and Emergency Campaigns

Build:

stock thresholds
low stock alerts
eligible donor targeting
emergency campaigns
automatic notifications

Goal:

Respond quickly to blood shortages.

Phase 11 — Public Website

Build:

landing page
about page
campaigns page
blood centers page
download app page
contact page

Goal:

Create a professional public-facing website without donor login.

Phase 12 — Reports and Analytics

Build:

donor reports
donation reports
center reports
inventory reports
campaign reports
regional analytics

Goal:

Give NBTS/admins decision-making data.

Phase 13 — Testing and Deployment

Build:

API tests
role permission tests
donation workflow tests
inventory workflow tests
deployment setup
production environment

Goal:

Prepare system for real use.

14. Required Laravel Tools and Packages

Use:

Laravel 12
Filament v3
Laravel Sanctum
Spatie Laravel Permission
PostgreSQL or MySQL
Redis
Predis
Tailwind CSS
Alpine.js
Laravel Queues
Laravel Notifications
Laravel Mail
QR code package
API Resources
Laravel Policies
Laravel Jobs
Laravel Events
Laravel Scheduler
15. Important Agent Instructions

The AI coding agent must:

follow this requirements document
build feature by feature
avoid creating donor web login
keep donor actions inside Flutter APIs
keep staff/admin actions inside Filament
update changelog.md after every major change
ask questions when requirements are unclear
maintain clean Laravel architecture
use services/actions for business logic
use policies for role access control
use API resources for mobile responses
use audit logs for sensitive changes
16. Changelog Rule

Create and maintain:

changelog.md

Every update must include:

date
feature added
files changed
database changes
API changes
known issues
next task