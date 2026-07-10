# NBTS Public User View Task Tracker

Last updated: 2026-07-09

## Purpose

Use this file to track work on the public/user-facing frontend pages and the backend data needed to support them.

Primary rule: do not invent NBTS information. Public copy, contact details, donor rules, services, regional contacts, news, and publication categories must come from the official NBTS Tanzania website or from backend records entered by staff.

Official source checked: https://www.nbts.go.tz/

## Official Website Notes Captured

### Identity

- Official institution name: National Blood Transfusion Service / Mpango wa Taifa wa Damu Salama.
- Parent institution shown on the official site: Ministry of Health / Wizara ya Afya.
- NBTS Tanzania started in 2004.
- NBTS coordinates blood safety activities in Tanzania.
- NBTS works with national and local government health structures.

### Vision And Mission

- Vision summary: ensure safe blood and blood products meet national and international standards.
- Mission summary: make enough safe blood available to save lives in Tanzania.

### Core Services

- Voluntary blood donor recruitment and blood collection.
- Whole blood donation.
- Apheresis donation for selected blood components.
- Laboratory testing of collected blood.
- Blood group testing using ABO and Rh.
- Testing for transfusion-transmitted infections including HIV, hepatitis B, hepatitis C, and syphilis.
- Blood product preparation, including red cells, plasma, platelets, and cryoprecipitate.
- Cold-chain storage for blood and blood products.
- Supply of safe blood and blood products to registered health facilities.
- Guidance to health facilities on proper use of blood and blood products.
- Quality management, proficiency testing, training, and quality control.

### Donor Eligibility

- Age: 18 to 65 years.
- Weight: at least 50 kg.
- Hemoglobin: at least 12.5 g/dL.
- Donor should be in good health.
- Men can usually donate again after about 3 months.
- Women can usually donate again after about 4 months.
- Staff must make the final donor safety decision at the center.

### Deferral Reasons

- Under 18 or over 65.
- Weight below 50 kg.
- Hemoglobin below 12.5 g/dL.
- Not in good health.
- Known conditions or infections that may make donation unsafe.
- Recent donation before the required interval.
- Aspirin or similar medicines within 72 hours when platelets or platelet-pheresis are intended.

### Donation Process

- Reception.
- Registration and demographic details.
- Weight check.
- Hemoglobin check.
- Health questionnaire.
- Donation if eligible.
- Refreshments and at least 15 minutes of rest.

### Apheresis Notes

- Apheresis collects selected blood components such as platelets, plasma, and red blood cells.
- The machine separates blood, collects the needed component, and returns the rest to the donor.
- Donor safety is monitored by trained staff.
- General apheresis eligibility is similar to whole blood donation.
- Extra apheresis thresholds from the official site:
  - Platelet apheresis: platelet count above 150 x 10^9/L.
  - Plasma apheresis: total protein above 60 g/L.
  - Double red cell apheresis: hemoglobin at least 14.0 g/dL.

### FAQ Facts

- Blood cannot currently be manufactured outside the human body.
- Donation should use sterile/safe equipment and should not expose the donor to HIV.
- Donated blood is tested for blood level, HIV, syphilis, hepatitis B and C, and blood group.
- Test results are handled confidentially.
- Avoid heavy exercise or lifting immediately after donation; follow staff advice.

### Official Contact Details

- Postal address: P.O. Box 65019, Dar es Salaam.
- Phone: 2181873.
- Mobile: +255 739 613 000.
- Email: info.nbts@afya.go.tz.
- Working hours: Monday to Friday, 07:30 to 15:30.
- Weekends and public holidays: 09:00 to 13:00.
- Emergency services: shown as 00:00 to 00:00 on the official site, so display as 24-hour emergency service only if NBTS confirms that interpretation.

### Regional Contact Details Captured

These are shown on the official site as zone/region contacts. Treat them as regional contacts, not full blood center records, unless NBTS confirms they are donation centers.

- Eastern Zone: Dar es Salaam 0719174551, Pwani 0763806082, Morogoro 0784421081.
- Western Zone: Tabora 0797986111, Kigoma 0769914977, Katavi 0682878470.
- Northern Zone: Arusha 0713819450, Kilimanjaro 0754269776, Manyara 0786062043, Tanga 0713821524.
- Lake Zone: Shinyanga 0754943237, Geita 0763706596, Mwanza 0765807383, Simiyu 0753697572, Kagera 0713528443, Mara 0784398809.
- Southern Highlands Zone: Mbeya 755909510, Songwe 0679955314, Iringa 0713492911, Ruvuma 0767678834, Rukwa 07768688227, Njombe 0756683046.
- Central Zone: Singida 0757032830, Dodoma 0766492563.
- TPDF Zone: Dar es Salaam 0719174551.
- Southern Zone: official page needs another verification pass before adding contact details.

### Official Content Areas

- Home.
- About the program.
- Vision and mission.
- Governance structure.
- Leadership.
- Services: blood collection, laboratory, proper use of blood, quality management.
- Blood donors: where to donate, centers, collection schedules, why donate, who can donate, process, deferral reasons, apheresis, patient testimonials, FAQ.
- News center: photos, videos, news, public releases, speeches.
- Publications: reports, guidelines, education publications, strategic plan, campaigns.
- Customer service: feedback, customer service charter, complaints.
- Staff links.
- Upcoming events.
- Flyers/brochures.
- Regional offices.

## Current App Status

### Frontend Already Present

- [x] Shared public layout with header, navigation, mobile menu, footer, and app CTA.
- [x] Home page: `/`.
- [x] About page: `/about`.
- [x] Donate page: `/donate`.
- [x] Services page: `/services`.
- [x] Eligibility guidance page: `/eligibility`.
- [x] Public impact/analytics page: `/analytics`.
- [x] Blood centers list: `/centers`.
- [x] Blood center detail: `/centers/{center}`.
- [x] Campaigns list: `/campaigns`.
- [x] Campaign detail: `/campaigns/{campaign}`.
- [x] News list: `/news`.
- [x] Publications landing page: `/publications`.
- [x] FAQ page: `/faq`.
- [x] Contact page: `/contact`.
- [x] Download app page: `/download-app`.

### Public Page Redesign Progress

- [x] Home page redesigned in the Pharma Clean visual system with Swahili-first content and 5 generated local images.
- [x] About page redesigned in the Pharma Clean visual system with Swahili-first content and 3 generated local images.
- [x] Donate page redesigned in the Pharma Clean visual system with Swahili-first content and 3 generated local images.
- [x] Centers page redesigned in the Pharma Clean visual system with Swahili-first content, backend-driven center records, city filters, and 3 generated local fallback images.
- [x] Services page redesigned in the Pharma Clean visual system with Swahili-first content and no new image generation.
- [ ] Eligibility page redesign pass.
- [x] Campaigns page redesigned in the Pharma Clean visual system with Swahili-first content, backend-driven campaign records, search, status/type/target filters, featured campaign, detail pages, linked center summary, and related campaigns.
- [x] News page redesigned in the Pharma Clean visual system with backend-driven articles, search, category filters, pagination, featured article, and article detail pages.
- [x] Publications page redesigned in the Pharma Clean visual system with Swahili-first content, no fake downloadable files, and clear backend publication requirements.
- [x] FAQ page redesigned in the Pharma Clean visual system with Swahili-first donor eligibility, safety, testing, app, and center guidance.
- [x] Contact page redesigned in the Pharma Clean visual system with official NBTS contacts, working hours, support areas, and backend-driven active center records.
- [x] Download app page redesigned in the Pharma Clean visual system with Swahili-first app guidance, local app screenshot, verified-link availability status, donor workflow, and support actions.

### Backend Already Present

- [x] Blood centers table and model with public directory fields.
- [x] Campaigns table and model with status, target blood group, and emergency campaign support.
- [x] Articles table and model for published news/education content.
- [x] News management system with Filament ArticleResource, slugs, featured articles, cover image upload, PDF/document attachments, source fields, and public URLs.
- [x] Public controllers for home, centers, campaigns, news, contact, eligibility, and analytics.
- [x] API routes for campaigns, articles, blood centers, donor profile, donor card, eligibility, loyalty, donations, appointments, notifications, and staff workflows.
- [x] Filament resources for centers, campaigns, donations, appointments, inventory, users, roles, permissions, eligibility records, and related admin workflows.
- [x] Demo seeders for centers, articles, campaigns, donor profiles, appointments, donations, inventory, and loyalty data.

## Main Gaps To Fix

### Information Accuracy

- [ ] Re-check the official Southern Zone page and add only verified contacts.
- [ ] Review all public page text against official source notes above.
- [ ] Label old official statistics with their exact year if used. Do not present old 2020/2021 or 2022/2023 numbers as current.
- [ ] Remove or clearly mark generic donor advice that is not directly sourced from NBTS.
- [ ] Decide whether the public site should be English-only, Kiswahili-only, or bilingual.

### Frontend Work

- [ ] Audit every public page for mobile and desktop layout quality.
- [ ] Make page copy consistent across Home, About, Donate, Services, Eligibility, FAQ, and Contact.
- [ ] Add a regional contacts section using verified official zone/region contacts.
- [ ] Add a customer service area for feedback, complaints, and customer service charter.
- [x] Add a public news detail page so users can open a full article from `/news`.
- [ ] Make `/publications` data-driven with filters and download actions.
- [ ] Add a public announcements/releases area if NBTS staff will publish notices.
- [ ] Add upcoming events or collection schedules if there is a reliable backend source.
- [x] Replace placeholder app store buttons on `/download-app` with a clear verified-link pending state.
- [ ] Confirm all images are appropriate, locally available, and not misleading.
- [ ] Verify no page uses non-Tanzania demo content.

### Backend Work

- [ ] Add a `publications` table with title, category, summary, file path, publish date, status, and optional cover image.
- [ ] Add a Filament publication resource for staff management.
- [ ] Add a public publications controller and dynamic `/publications` listing.
- [ ] Add an API endpoint for mobile publications if needed.
- [x] Add a public article detail route and controller method.
- [ ] Decide whether regional contacts belong in `blood_centers` or a separate `regional_contacts` table.
- [ ] Seed verified Tanzania-only regional contact data after the source pass.
- [ ] Clean old demo seed cleanup references that mention Kenya test data.
- [ ] Add customer feedback/complaint storage if the website will accept submissions.
- [ ] Align public eligibility quiz logic with official male/female donation intervals.
- [ ] Add tests for public routes, publication filtering, article detail, and eligibility copy/logic.

### Content/Data Entry Work

- [ ] Enter or seed official NBTS news titles/dates only if NBTS confirms they should appear in this system.
- [ ] Enter official publications only after files or URLs are available.
- [ ] Confirm real app store URLs, package names, and QR code target.
- [ ] Confirm if regional phone contacts can be displayed publicly in the new app.
- [ ] Confirm whether NBTS wants governance and leadership pages separate or merged into About.

## Suggested Implementation Order

1. Information accuracy pass on all existing public pages.
2. Eligibility page fix for male/female donation intervals.
3. Regional contacts model/seed or verified static section.
4. News detail page.
5. Publications backend and frontend.
6. Customer service/feedback and complaints workflow.
7. Download app real links and QR.
8. Full responsive QA and route tests.

## Source Pages Checked

- https://www.nbts.go.tz/
- https://www.nbts.go.tz/who-are-we
- https://www.nbts.go.tz/vision-and-mission
- https://www.nbts.go.tz/donation
- https://www.nbts.go.tz/laboratory
- https://www.nbts.go.tz/clinical-interface
- https://www.nbts.go.tz/quality-management-system
- https://www.nbts.go.tz/why-donate
- https://www.nbts.go.tz/who-can-donate
- https://www.nbts.go.tz/donation-process
- https://www.nbts.go.tz/deferral-criteria
- https://www.nbts.go.tz/apheresis-donation
- https://www.nbts.go.tz/faq
- https://www.nbts.go.tz/news
- https://www.nbts.go.tz/releases
- https://www.nbts.go.tz/eastzones
- https://www.nbts.go.tz/westzones
- https://www.nbts.go.tz/nothernzones
- https://www.nbts.go.tz/zones
- https://www.nbts.go.tz/southhighzones
- https://www.nbts.go.tz/central
- https://www.nbts.go.tz/tpdf

## Current Working Notes

- The existing frontend is already beyond a basic scaffold; it has a public website structure with most required pages.
- The most important next step is not adding more pages blindly. It is making the existing pages accurate, data-backed, and production-clean.
- The official website has some sparse pages and possible typos. When the official site is unclear, leave the item as "needs NBTS confirmation" instead of guessing.
