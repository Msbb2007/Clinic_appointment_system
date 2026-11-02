<div align="center">

# 🏥 Enterprise Clinic Management System
### A Scalable, Secure, and Event-Driven Clinic Management Architecture

[![Laravel](https://img.shields.io/badge/Laravel-13.x/11.x-red?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15%2B-blue?style=for-the-badge&logo=postgresql)](https://www.postgresql.org)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-purple?style=for-the-badge&logo=php)](https://www.php.net)

</div>

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
