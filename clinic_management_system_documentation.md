# 🏥 Enterprise Clinic Management System

### A Scalable, Secure, and Event-Driven Clinic Management Architecture

```markdown
# 🏥 Enterprise Clinic Management System

### A Scalable, Secure, and Event-Driven Clinic Management Architecture

[![Laravel](https://img.shields.io/badge/Laravel-10.x/11.x-red?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15%2B-blue?style=for-the-badge&logo=postgresql)](https://www.postgresql.org)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple?style=for-the-badge&logo=php)](https://www.php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE)

---

## 📖 About The Project
This project is an enterprise-grade clinic management system designed to handle online appointment bookings, advanced doctor schedule management, and 100% data integrity under high-concurrency conditions. The system architecture is built upon modern design patterns including **Observers**, **Events & Listeners**, and database-level transaction locking.

---

## 🚀 Core Features

* **🔐 Authentication & RBAC:** Multi-tier user management (Admin, Doctor, Patient) powered by Laravel Sanctum.
* **📅 Dynamic Scheduling:** Utilization of the iCalendar standard (`RRule`) to define recurring doctor shifts and availability slots.
* **⚡ Double Booking Prevention:** Implementation of exclusive row-level locking (`lockForUpdate`) combined with PostgreSQL Unique Constraints.
* **📝 Automated Audit Trails:** Automatic tracking of appointment status changes using Laravel Observers (`AppointmentLog`).
* **🔔 Queue-based Notifications:** Asynchronous email and database notifications for user registrations, doctor verifications, and cancellations.
* **🇮🇷 Full Localization:** Localized validation rules and error messages tailored for Persian-speaking users.

---

## 🛠️ Tech Stack

* **Backend Framework:** Laravel (PHP 8.2+)
* **Database:** PostgreSQL (with native support for Unique Constraints and Transaction Locking)
* **Queue & Background Jobs:** Laravel Queue (for asynchronous notification handling)
* **Scheduling Standard:** `rlanvin/php-rrule` for parsing and generating recurrence rules
* **Authentication:** Laravel Sanctum (API Tokens)

---

## 🏛️ Architecture

The system follows a layered, event-driven architecture pattern:
1. **Observers:** To automatically monitor model lifecycle changes (e.g., tracking appointment state transitions and logging audit data).
2. **Events & Listeners:** Decoupling business logic from notification dispatching, processed safely inside background queues (`ShouldQueue`).
3. **Database Concurrency Control:** Enforcing strict data consistency directly at the database engine level, independent of application runtime state.

---

## 🔒 Database Challenges & Concurrency Solutions

One of the biggest challenges in online booking systems is the **Race Condition** (where two concurrent users attempt to book the exact same slot within milliseconds of each other).

### Implemented Solutions:
1. **Database-Level Unique Index:** Applied a tactical composite unique index on the `appointments` table:
   ```sql
   UNIQUE (doctor_id, appointment_date, start_time)
   ```
2. **Database Transactions & Locking:** Wrapped booking operations inside `DB::transaction` using `lockForUpdate()` to serialize access to conflicting rows.
3. **PostgreSQL Error Handling:** Intercepting SQLSTATE error code `23505` (Unique Violation) at the controller level to gracefully abort and return a clean `409 Conflict` response to the colliding user.

---

## 🔄 End-to-End Workflow

The diagram below outlines the full lifecycle workflow from user onboarding to appointment management:

```text
[ User / Doctor Registration ]
       │
       ├──> [ If Doctor Role ] ──> Status: pending ──> [ Admin Alert Dispatched ]
       │                                                         │
       │                                            [ Admin Approves Profile ]
       │                                                         │
       │                                            [ Approval Email Sent to Doctor ]
       │
       └──> [ Set Work Schedule (RRule) ] ──> [ Dynamic Slot Generation in Calendar ]
                                                                │
                                                   [ Patient Booking Request ]
                                                                │
                                                   [ Concurrency Check (Lock & Unique Index) ]
                                                                │
                                            ┌───────────────────┴───────────────────┐
                                            ▼                                       ▼
                                   [ Success (Confirmed) ]               [ Collision (409 Conflict Error) ]
                                            │
                                            ├──> [ Automatic Audit Log via Observer ]
                                            │
                                            └──> [ Cancellation by Doctor / Patient (Event & Queue) ]
```

---

## ⚙️ Installation & Setup

1. Clone the repository:
```bash
git clone https://github.com/your-username/clinic-management-system.git
cd clinic-management-system
```

2. Install dependencies:
```bash
composer install
```

3. Setup environment file:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your **PostgreSQL** database credentials in `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_clinic_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

5. Run database migrations:
```bash
php artisan migrate
```

6. Run the local development server:
```bash
php artisan serve
```

---

## 📄 License
This project is licensed under the [MIT License](LICENSE).
```