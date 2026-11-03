# DirectStay Solutions Co.

> **Direct Condominium Rental & Automated Building Compliance Platform**  
> Servicing the Ortigas-Pasig Corridor (Anchored at Urban Deca Homes Ortigas)

---

## Quickstart & Setup Guide

For the full, step-by-step setup and execution instructions (including system requirements, database seeding, test accounts, and end-to-end workflows), please refer to:

👉 **[HOW_TO_RUN.md](./HOW_TO_RUN.md)**

### Quick Commands

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment & key
cp .env.example .env
php artisan key:generate

# 3. Database migrations & seeders
php artisan migrate --seed

# 4. Storage symlink & asset build
php artisan storage:link
npm run build

# 5. Run application
composer run dev
```

Application will be accessible at **http://localhost:8000** (or **http://directstay.test** if using Laragon).

---

## Pre-Configured Demo Accounts

| Role | Portal URL | Email | Password |
| :--- | :--- | :--- | :--- |
| **Host / Lessor** | `/host/login` | `host@directstay.local` | `password` |
| **Guest / Customer** | `/login` | `customer@directstay.local` | `password` |

---

## Core Features

* **Direct-Booking Catalog:** Browse units (Pad Dos N343 & Pad Uno P718) with real-time rate comparison against neighboring Ortigas condominiums.
* **Digital Compliance Portal (`/compliance/{bookingCode}`):** Guest roster registration, secure government ID verification (compliant with RA 10173 Data Privacy Act), and payment deposit upload.
* **Host Verification Triage (`/host/verifications`):** Streamed private document verification, booking approval/rejection workflow, and automated Building Gate Pass PDF generation.
* **Post-Checkout Inventory & Penalty Engine (`/host/checkouts/{booking}`):** Digital turnover checklist with automatic penalty and deposit deduction calculation.
* **Official Building PDF Integration:** Automated generation of Urban Deca Homes Ortigas Building N and Building P Gate Passes and Rental Agreements via DomPDF.
