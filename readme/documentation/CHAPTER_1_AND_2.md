# CHAPTER 1: INTRODUCTION

## Background of the Study

The short-term residential leasing and "staycation" market in the Philippines has expanded rapidly, with high-rise condominium complexes serving as prime accommodations for urban leisure, remote work, and transient travelers. Independent unit owners typically market their rental units through Online Travel Agencies (OTAs) such as Airbnb and Agoda. However, these platforms impose substantial intermediary costs: guests encounter platform service markups of 14% to 16%, while unit hosts lose an additional 3% or more in host deductions. This split-fee mechanism yields a cumulative intermediary take-rate of roughly 17% to 20%, significantly shrinking host revenues and raising prices for consumers.

Beyond steep platform fees, condominium leasing operations in the Philippines must comply with strict building administration and security protocols. At residential complexes such as Urban Deca Homes Ortigas Condominium Corporation, property administrators require advance filing of guest rosters, verified government identification copies, signed waivers adhering to building rules (such as quiet hours and strict anti-smoking policies), and authorized building clearance forms before granting lobby access. These administrative requirements vary across property management groups and different residential towers, meaning operational procedures cannot be managed with a one-size-fits-all approach.

Currently, micro-lessors managing properties across multiple buildings—such as Building P and Building N at Urban Deca Homes Ortigas, alongside external condominium developments—handle reservations and security compliance manually. Hosts coordinate with guests over unstructured messaging apps like Facebook Messenger to collect advance reservation deposits, verify photos of government IDs and selfies, and cross-reference inventory items. This manual workflow introduces significant operational friction:

* Sensitive personal documents (e.g., driver's licenses, passports, and facial photos) are exchanged over unencrypted social media channels without structured data storage.
* Gate pass clearance forms must be physically drafted or manually populated for each specific condominium tower to meet distinct lobby security rules.
* Financial tracking for the mandatory ₱1,000.00 advance security deposit, housekeeping dispatches, and penalty deductions (such as ₱500 for unthrown garbage or lost access cards) is maintained through informal personal mobile wallets without an integrated ledger.

To resolve these inefficiencies, **DirectStay** is developed as a web-based, multi-tenant direct-booking and property management platform built on PHP, Laravel, and MySQL. DirectStay provides independent condominium hosts with a dynamic booking engine, automated building-specific gate pass generation, an ID verification pipeline, and automated email operations, operating on a low 5% platform fee model.

## Statement of the Problem

Independent condominium staycation lessors encounter critical commercial and operational bottlenecks across multi-property operations:

1. **Excessive Intermediary Fees:** Relying on mainstream OTAs strips 17% to 20% in combined fees from each booking, reducing the lessor's profitability while increasing guest costs.
2. **Insecure Guest Verification and Privacy Risks:** Gathering identity credentials and selfies over informal chat applications exposes guests to data privacy risks and requires tedious human cross-checking.
3. **Fragmented Multi-Property Compliance:** Property management offices enforce building-specific clearance formats. Manually filling out distinct gate pass templates across different condominium developments results in check-in delays at the lobby gates.
4. **Manual Financial and Penalty Tracking:** Lessors lack a centralized ledger to track the mandatory ₱1,000.00 advance deposit, account for housekeeping services (₱500 basic / ₱1,300 deep clean), assess penalty deductions (₱500 garbage violation or lost keys), and log amenity add-on purchases.

## Objectives of the Project

### General Objective

To design, develop, and deploy a multi-tenant web application using PHP, Laravel, and MySQL that provides independent condominium lessors with direct booking management, dynamic building security compliance, automated email routing, and a low-commission pricing structure.

### Specific Objectives

1. **Develop a Multi-Tenant Direct-Booking Engine:** Construct a responsive web interface featuring a live reservation calendar that prevents date collisions across multiple properties and distinct condominium complexes.
2. **Implement an Automated Identity Verification & Document Portal:** Create a secure file-handling pipeline requiring guests to submit government-issued IDs, identity verification selfies, and digital signatures acknowledging building-specific house rules prior to confirmation.
3. **Automate Dynamic Gate Pass PDF Generation:** Build a template-switching module that compiles verified guest data, occupant rosters, and authorized representative signatures into the official clearance format required by each specific building (such as the Urban Deca Homes Ortigas Guest Form).
4. **Deploy Server-Side Automated Email Operations:** Utilize Laravel's built-in SMTP mailer to dispatch reservation confirmations, check-in instructions, WiFi credentials, and PDF gate passes directly to guests and property management without recurring SMS costs.
5. **Construct an Operational Financial and Penalty Ledger:** Build an administrative host dashboard that manages the ₱1,000.00 security deposit, reconciles optional amenity add-ons, deducts policy violation penalties, and manages post-checkout room inventory checklists.
6. **Establish an Entrepreneurial Micro-Commission Model:** Validate a 5% guest platform fee business model that allows hosts to retain 100% of their base room rates while generating recurring transaction revenue for the platform.

## Scope and Limitations

### Scope

* **Target Environment & Localization:** The system supports multi-property operations, managing units across Urban Deca Homes Ortigas (specifically Building P and Building N) and expanding dynamically to independent condominium units outside the Deca complex through a configurable database architecture.
* **Technical Architecture:** Built using **PHP and Laravel** for backend routing and controllers, with a **MySQL** relational database managing multi-tenant relations. Front-end interfaces are designed using responsive UI frameworks (Tailwind CSS/Bootstrap).
* **Communication Channel:** Booking notifications, guest check-in dossiers, and PDF gate pass attachments are transmitted via Laravel SMTP email routing.
* **Document and Compliance Processing:** The system dynamically selects and renders building-specific clearance forms (PDFs) based on the unit's assigned property record.
* **Financial Ledger Modules:** Tracks the ₱1,000.00 advance security deposit, guest-selected amenity add-ons (extra pillows, towels, bed linens), housekeeping service fees (₱500 basic / ₱1,300 deep clean), and violation penalties (₱500 unthrown garbage fee, ₱500 lost key/elevator card fee).

### Limitations

* **Payment Settlement:** The platform does not incorporate third-party merchant acquiring for credit card processing; it uses a proof-of-payment workflow where guests upload GCash or bank transfer receipts for host verification.
* **Facial Verification:** Guest ID photos and live selfies are stored securely and rendered side-by-side on the host dashboard for visual human confirmation by the lessor, rather than using automated biometric AI algorithms.
* **SMS Integration:** Traditional cellular SMS gateways are intentionally excluded to keep operational overhead at zero.
* **External Property Integration:** The system operates as an independent host portal and does not interface directly with the private ERP databases of external condominium corporations.

## Significance of the Study

* **For Condominium Lessors:** Eliminates the 15% to 20% commission penalty of commercial OTAs, streamlines multi-building gate pass clearance, and centralizes security deposit reconciliations.
* **For Staycation Guests:** Provides a lower-cost booking alternative with only a 5% service fee (saving an average of ₱276 per ₱3,000 booking compared to Airbnb), while protecting personal identity documents through a structured, secure web application.
* **For Building Security and Administration:** Ensures every transient visitor is properly documented with verified identity records and signed waivers prior to arrival at the lobby.
* **For the Student Developers:** Demonstrates the application of relational database modeling, dynamic PDF rendering, and SaaS business strategy to solve real-world operational bottlenecks in the Philippine hospitality sector.

---

# CHAPTER 2: CONCEPTUAL FRAMEWORK AND BUSINESS MODEL

## Review of Related Systems and Literature

The short-term residential leasing sector relies on models that fail to address the specific regulatory requirements of Philippine condominiums:

1. **Global Online Travel Agencies (Airbnb, Agoda):** These platforms provide international guest reach and automated checkout. However, they impose substantial fees: guests pay a 14% to 16% markup, while hosts lose 3% from their payout. Crucially, global platforms do not interface with local building administration workflows, forcing hosts to manually compile guest forms and collect external security deposits.
2. **Enterprise Property Management Systems (Guesty, Cloudbeds):** While capable of multi-calendar synchronization, enterprise PMS platforms are engineered for large boutique hotels. Their pricing models and complex onboarding make them inaccessible to micro-lessors operating a small number of units across separate towers.
3. **Informal Social Commerce (Facebook Marketplace/Groups):** Many local hosts attempt direct bookings via social media to avoid platform commissions. However, this approach lacks operational infrastructure: reservation dates are tracked manually, advance deposits are reconciled informally, and gate pass forms must be written by hand for each lobby guard, creating severe operational bottlenecks.

**DirectStay** bridges this market gap by delivering an agile, multi-tenant B2B SaaS platform. It combines an OTA-style live booking engine with dynamic, localized building compliance automation.

## Conceptual Framework (IPO Model)

The Input-Process-Output (IPO) framework outlines the technical flow and operational transitions of the DirectStay web platform.

| Input | Process | Output |
| --- | --- | --- |
| **Guest Data:**<br>• Full legal name, email, contact number<br>• Selected condo unit (e.g., Deca Bldg P, Deca Bldg N, or external unit)<br>• Reservation dates & total authorized occupant roster<br><br>**Compliance & Verification Media:**<br>• Front/back government-issued ID image<br>• Real-time identity verification selfie<br><br>**Financial Entries:**<br>• ₱1,000.00 advance security deposit<br>• GCash proof-of-payment screenshot<br>• Selected amenity add-ons (extra linens, pillows)<br><br>**Property Parameters:**<br>• Specific building gate pass PDF template path<br>• Building admin/guard email address<br>• Digital host signature & specific house rules | **Laravel Backend Logic:**<br>• Date collision validation via MySQL database<br>• Calculation of room rate, 5% guest platform fee, and deposits<br>• Secure image upload routing to `storage/app/private`<br>• Dynamic PDF document compilation mapped to the unit's specific building template<br>• Status workflow transitions (Pending &rarr; Verified &rarr; Checked In &rarr; Completed)<br>• Automated deduction engine for logged penalties (₱500 garbage, ₱500 lost key)<br>• Inventory checklist state verification<br>• Automated SMTP email dispatch | **For the Guest:**<br>• Instant booking confirmation with reference code<br>• Check-in dossier sent via email with unit WiFi credentials, delivery points, and gate pass<br><br>**For the Unit Lessor/Host:**<br>• Centralized multi-property management dashboard<br>• Side-by-side ID and selfie visual verification tool<br>• Itemized security deposit accounting ledger and penalty deduction invoices<br><br>**For Building Security/Administration:**<br>• Formatted PDF Gate Pass/Guest Form routed directly to lobby email or presented digitally by guest |

## The Entrepreneurial Business Model

### Value Proposition

DirectStay delivers a balanced economic and operational value proposition:

* **For Condominium Hosts:** Retain 100% of their base room earnings without losing a 3% commission to OTAs, manage multiple properties through a single interface, and automate gate pass generation for any condominium tower.
* **For Guests:** Avoid excessive 14% to 16% OTA service markups by paying a modest 5% platform fee, while enjoying a streamlined, secure digital check-in that submits required building clearances in advance.

### Comparative Economic Breakdown

On a standard **₱3,000.00** staycation reservation, DirectStay delivers clear financial advantages over commercial OTA split-fee structures:

| Transaction Feature | Airbnb (Standard Split-Fee) | DirectStay (5% Fee Model) | Economic Benefit |
| --- | --- | --- | --- |
| **Guest Platform Fee** | +₱426.00 (~14.2%) | +₱150.00 (5.0%) | **Guest saves ₱276.00** |
| **Host Deduction** | -₱90.00 (3.0%) | ₱0.00 (0.0% Commission) | **Host keeps ₱90.00 more** |
| **Total Intermediary Take** | **₱516.00 (~17.2%)** | **₱150.00 (5.0%)** | **DirectStay operates on a lean take-rate** |
| **Lobby Clearance Form** | ❌ Manual chat coordination | ✅ Automated PDF submission | Eliminates check-in delays |

### Target Market Strategy

* **Initial Beachhead Market:** Micro-lessors managing staycation units within Urban Deca Homes Ortigas (specifically Buildings P and N), leveraging real operational data, established house rules, and official Condominium Corporation Guest Form templates.
* **Expansion Market:** Independent unit owners and small-scale property managers operating within external high-density residential developments in Metro Manila (such as SMDC, DMCI, or Suntrust condominiums) that enforce gate clearance protocols.

### Revenue Streams

The platform monetizes its software infrastructure through two primary channels:

1. **The 5% Guest Platform Service Fee:** Directly added to the guest's checkout bill on every confirmed booking. This low fee covers server operations, automated email routing, and platform maintenance without deducting revenue from the host's base nightly rate.
2. **Onboarding and Document Digitization Fee (Optional):** A one-time setup fee of ₱1,500.00 for lessors who require professional onboarding, including custom PDF gate pass template development for new condominium buildings, room inventory digitization, and house rules configuration.

### Operational Cost Structure

DirectStay is structured to maximize profit margins through minimal operational overhead:

* **Web Hosting & Domain:** Cloud hosting infrastructure and custom domain registration.
* **Private Storage:** Scaled server storage for guest identity images and payment receipts within protected system directories.
* **Email Infrastructure:** Utilizing Laravel's native SMTP drivers to send transactional booking confirmations and PDF documents at no per-message cost, keeping variable expenses near zero.

### Data Privacy and Security Compliance

DirectStay processes sensitive identity media, including Philippine government-issued identification cards, verification selfies, and residential addresses. The platform's technical architecture is built to comply with the **Philippine Data Privacy Act of 2012 (Republic Act No. 10173)**:

* Identity files are saved to non-public server storage (`storage/app/private/`) and cannot be accessed via direct web URLs.
* Documents are rendered exclusively to authenticated host accounts via temporary, cryptographically signed routes.
* Personal identification details are collected solely to satisfy the legitimate security clearance requirements established by condominium corporation administrators.
