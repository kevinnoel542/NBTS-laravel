# Development Phases

This document follows the system requirements in `Blood Donation Platform - System Requirements.md`.

## Phase 1 - Core Foundation

Build:
- Roles and permissions
- Users
- Donor profiles
- Blood centers
- Center staff
- Authentication
- Filament access control

Goal: create the main system structure.

## Phase 2 - Donor API and Mobile Support

Build:
- Register API
- Login API
- Logout API
- Profile API
- Donor card API
- Blood centers API
- Campaigns API

Goal: allow the Flutter app to connect and authenticate.

## Phase 3 - Appointment and Walk-In System

Build:
- Appointment booking
- Appointment status management
- Walk-in donation support
- Center appointment view

Goal: allow donors to book appointments or walk in.

## Phase 4 - Donation Recording System

Build:
- Donor search
- QR lookup
- Donor ID lookup
- Phone/name lookup
- Donation recording
- Donation history
- Blood group verification

Goal: allow staff to record real donations correctly.

## Phase 5 - Eligibility Engine

Build:
- Eligibility status
- Next eligible donation date
- Temporary deferrals
- Permanent deferrals
- Age, weight, and health checks

Goal: prevent notifications and donations for ineligible donors.

## Phase 6 - Digital Donor Card

Build:
- Donor ID generator
- QR code generator
- Donor card API
- Mobile donor card data

Goal: allow fast donor verification.

## Phase 7 - Loyalty Program

Build:
- Badges
- Donor levels
- Milestone tracking
- Rewards
- Donor reward history
- Leaderboards

Goal: increase donor retention and motivation.

## Phase 8 - Notification Engine

Build:
- Push notification preparation
- SMS system
- Email system
- Notification templates
- Notification history

Goal: keep donors engaged and informed.

## Phase 9 - Blood Unit and Inventory Management

Build:
- Blood unit creation
- Unit lifecycle tracking
- Inventory totals
- Manual inventory adjustments
- Expiry handling
- Disposal confirmation

Goal: track real blood supply per center.

## Phase 10 - Low Stock and Emergency Campaigns

Build:
- Stock thresholds
- Low stock alerts
- Eligible donor targeting
- Emergency campaigns
- Automatic notifications

Goal: respond quickly to blood shortages.

## Phase 11 - Public Website

Build:
- Landing page
- About page
- Campaigns page
- Blood centers page
- Download app page
- Contact page

Goal: create a professional public-facing website without donor login.

## Phase 12 - Reports and Analytics

Build:
- Donor reports
- Donation reports
- Center reports
- Inventory reports
- Campaign reports
- Regional analytics

Goal: give NBTS/admins decision-making data.

## Phase 13 - Testing and Deployment

Build:
- API tests
- Role permission tests
- Donation workflow tests
- Inventory workflow tests
- Deployment setup
- Production environment

Goal: prepare the system for real use.
