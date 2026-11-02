<div align="center">

# 🏥 Enterprise Clinic Management API
### A Scalable, Event-Driven REST API for Doctor Appointment Booking

[![Laravel](https://img.shields.io/badge/Laravel-13-red?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15%2B-blue?style=for-the-badge&logo=postgresql)](https://www.postgresql.org)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-purple?style=for-the-badge&logo=php)](https://www.php.net)

</div>

---

## 📖 About The Project

This repository houses a headless, enterprise-grade RESTful API built for high-concurrency medical appointment booking and doctor scheduling. Designed with **Laravel** and **PostgreSQL**, it contains no web frontend—all interactions are handled via JSON endpoints (documented in the included Postman collection). 

The system relies heavily on modern architectural patterns including **Observers**, **Queue-backed Event-Listeners**, **iCalendar Recurrence Parsing (RRule)**, and **Database-Level Transaction Locking** to guarantee 100% data integrity under heavy concurrent traffic.

---

## 🚀 Role-Based Features

### 👨‍⚕️ Doctor Capabilities
* Manage personal medical profile (bio, specialty, medical council code).
* Define dynamic recurring work availability (`RRule`)  with start/end times and slot durations.
* Register leaves and temporary unavailability periods.
* Track daily appointment schedules, update appointment statuses (`completed`, `cancelled`), or force-delete incorrect entries.

### 👥 Patient Capabilities
* Search and list verified doctors by specialty or name.
* Fetch dynamically generated free time slots for up to 30 days ahead.
* Instantly book, list, view details, or cancel upcoming appointments.
* Receive real-time database and email notifications for booking events or cancellations.

### 🛡️ Admin Capabilities
* Approve or reject pending doctor registration requests.
* Provision new doctor accounts and profiles atomically within single database transactions.
* Full CRUD over specialties and system users.
* Access platform-wide audit logs, status histories, and aggregate operational reports.

---

## 🛠️ Tech Stack & Standards

* **Backend Framework:** Laravel 13 (PHP 8.3+)
* **Database Engine:** PostgreSQL 15+ (Utilizing native row-locking & composite unique index constraints)
* **Authentication:** Laravel Sanctum (`Authorization: Bearer <token>`)
* **Background Processing:** Laravel Queues (Database driver for asynchronous email & notification dispatches)
* **Scheduler:** Laravel Task Scheduling (Periodic appointment reminder processing)
* **Scheduling Engine:** `rlanvin/php-rrule` (iCalendar RFC 5545 compliance)
* **Default Timezone:** `Asia/Tehran`

---

## 📊 Data & Response Formats

All API responses are returned as standardized JSON payloads. Endpoint lists feature standard Laravel pagination wrapper metadata alongside HTTP status codes (`401 Unauthenticated`, `403 Forbidden`, `404 Not Found`, `409 Conflict`, `422 Unprocessable Entity`).

### Standard Response Structure (e.g., Patient Appointments List)

```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 4,
                "doctor_name": "احمد مسعودی",
                "specialty": "قلب و عروق",
                "appointment_date": "2026-11-07",
                "start_time": "09:00:00",
                "end_time": "09:30:00",
                "status": "cancelled",
                "fee": "350000",
                "created_at": "2026-10-31 13:13:08"
            },
            {
                "id": 2,
                "doctor_name": "احمد مسعودی",
                "specialty": "قلب و عروق",
                "appointment_date": "2026-11-02",
                "start_time": "12:00:00",
                "end_time": "12:30:00",
                "status": "cancelled",
                "fee": "350000",
                "created_at": "2026-10-24 23:02:05"
            },
            {
                "id": 3,
                "doctor_name": "احمد مسعودی",
                "specialty": "قلب و عروق",
                "appointment_date": "2026-11-02",
                "start_time": "09:00:00",
                "end_time": "09:30:00",
                "status": "pending",
                "fee": "350000",
                "created_at": "2026-10-31 13:09:29"
            },
            {
                "id": 1,
                "doctor_name": "احمد مسعودی",
                "specialty": "قلب و عروق",
                "appointment_date": "2026-10-31",
                "start_time": "12:00:00",
                "end_time": "12:30:00",
                "status": "cancelled",
                "fee": "350000",
                "created_at": "2026-10-24 22:39:49"
            }
        ],
        "first_page_url": "http://127.0.0.1:8000/api/patient/appointments?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://127.0.0.1:8000/api/patient/appointments?page=1",
        "links": [
            {
                "url": null,
                "label": "pagination.previous",
                "page": null,
                "active": false
            },
            {
                "url": "http://127.0.0.1:8000/api/patient/appointments?page=1",
                "label": "1",
                "page": 1,
                "active": true
            },
            {
                "url": null,
                "label": "pagination.next",
                "page": null,
                "active": false
            }
        ],
        "next_page_url": null,
        "path": "http://127.0.0.1:8000/api/patient/appointments",
        "per_page": 10,
        "prev_page_url": null,
        "to": 4,
        "total": 4
    }
}
```

## 📐 Deep-Dive Design Decisions

### 1. User & Identity Architecture (RBAC)
* A unified `users` table holds core identity credentials and a `role` enum (`admin`, `doctor`, `patient`). Detailed medical attributes (council registration code, specialty ID, bio) reside in a dedicated `doctors` table linked via foreign key.
* Public self-registration assigns the `patient` role by default. Admin-initiated doctor setup handles user creation, profile attachment, and initial verification state within a single atomic database transaction.
* Accounts utilize **Soft Deletes**. Deleting a doctor cascades account revocation and token invalidation. To preserve scheduling integrity, accounts with active upcoming appointments cannot be soft-deleted (`409 Conflict`). Email addresses and medical council codes of deleted entities remain permanently reserved in audit logs.

### 2. On-the-Fly Slot Generation (RRule Engine)
* Time slots are **never statically pre-stored in the database**. Doctors define weekly recurrence rules (specifying days of the week, repeat intervals, active working hours, and slot lengths). The server processes these parameters into an RFC-compliant RRule string (e.g., `FREQ=WEEKLY;INTERVAL=1;BYDAY=MO,WE;WKST=SA`).
* Slots are generated dynamically upon client request (evaluating up to 30 days into the future). The scheduling engine generates raw time slots, subtracts recorded doctor leave dates, and filters out non-cancelled existing appointments. Past time windows are automatically ignored.

### 3. Concurrency Control & Double Booking Prevention
Online booking systems face critical **Race Conditions** when multiple users attempt to claim a single slot in the exact same millisecond.

* **Database-Level Unique Index:** A composite unique index on the `appointments` table acts as a hardware-level safety net:
  ```sql
  UNIQUE (doctor_id, appointment_date, start_time)
  ```
* **Pessimistic Locking & Transactions:** Booking requests execute inside a `DB::transaction`. The system queries active entries using `lockForUpdate()`, evaluating whether the requested slot is still vacant.
* **PostgreSQL Exception Interception:** If two transactions bypass application checks simultaneously, PostgreSQL rejects the second insertion with SQLSTATE `23505` (Unique Violation). The application catches this exception and returns a graceful `409 Conflict` JSON response (*"This time slot was just booked by another patient."*).

### 4. Lifecycle Observers, Queues, & Reminders
* **Audit Trail Observer:** An `AppointmentObserver` intercepts model lifecycle events, writing entry records to `appointment_logs` (tracking initial creation, pending state, completed state, or cancellations).
* **Decoupled Event Queue:** `AppointmentBooked` and `AppointmentCancelledByDoctor` events dispatch asynchronously. Queue listeners handle background email and database notifications (`ShouldQueue`) to prevent blocking the HTTP response cycle. Cancellations notify the counterpart party automatically.
* **Automated Reminders:** A scheduler job executes every 15 minutes, inspecting confirmed appointments occurring within the next 24 hours that haven't received a reminder. It dispatches a `SendAppointmentReminder` queue job and marks `reminder_sent_at` to prevent duplicate notifications.

---

## 🔄 End-to-End Workflow

```text
[ User / Doctor Self-Registration ]
       │
       ├──> [ Doctor Account Created ] ──> Status: pending ──> [ Admin Alert Triggered ]
       │                                                                 │
       │                                                    [ Admin Reviews & Approves ]
       │                                                                 │
       │                                                    [ Verification Email Dispatched ]
       │
       └──> [ Doctor Defines Work Schedule ] ──> [ Server Parses RRule String ]
                                                                 │
                                                    [ Patient Requests Available Slots ]
                                                                 │
                                                    [ On-The-Fly Slot Generation (30 Days Out) ]
                                                                 │
                                                    [ Patient Submits Booking Request ]
                                                                 │
                                                    [ DB Transaction + lockForUpdate() ]
                                                                 │
                                             ┌───────────────────┴───────────────────┐
                                             ▼                                       ▼
                                   [ Success (Confirmed) ]               [ Collision (409 Conflict Error) ]
                                             │
                                             ├──> [ Status Logged via AppointmentObserver ]
                                             │
                                             └──> [ Queued Email / DB Notifications Dispatched ]
```

---

## 🗄️ Database Schema Overview

```text
  +------------------+         +--------------------+         +-----------------------+            +-----------------------+
  |      users       |         |      doctors       |         | doctor_availablities  |            |   doctor_exceptions   |
  +------------------+         +--------------------+         +-----------------------+            +-----------------------+
  | id (PK)          |<-------1| id (PK)            |<-------1| id (PK)               |1-------->  | id (PK)               |
  | username         |         | user_id (FK)       |         |                       |            | doctor_id (FK)        |
                               | full_name          |         | doctor_id (FK)        |            | rrule_string          |
  | email (Unique)   |         | specialty_id (FK)  |         | rrule_string          |            | start_time / end_time |         
  | phone (Unique)   |         | medical_code       |         | start_time / end_time |            | slot_duration         |
  | password         |         | verification_status|         | slot_duration         |            +-----------------------+
  | role ()          |         +--------------------+         +-----------------------+
  +------------------+                   ^        |
           ^                             |        |
           |                             |        |
           |                             |        v
  +------------------+                   |       +------------------+  
  |   patients       |                   |       |   specialties    |   
  +------------------+                   |       +------------------+  
  | id (PK)          |                   |       | id (PK)          |  
  | user_id (FK)     |                   |       | name (FK)        | 
  | full_name        |                   |       | description      | 
  | national_code    |                   |       +------------------+  
  +------------------+                   |
           ^                             |
           |                             |                                
  +-----------------------------------------------------------------------------------+
  |                                 appointments                                      |
  +-----------------------------------------------------------------------------------+
  | id (PK)                                                                           |
  | patient_id (FK)                                                                   |
  | doctor_id (FK)                                                                    |
  | appointment_date                                                                  |
  | start_time / end_time                                                             |
  | status (pending, completed, cancelled)                                            |
  | CONSTRAINT: UNIQUE(doctor_id, appointment_date, start_time)                       |
  +-----------------------------------------------------------------------------------+
                                         |
                                         v
                      +--------------------------------------+
                      |           appointment_logs           |
                      +--------------------------------------+
                      | id (PK)                              |
                      | appointment_id (FK)                  |
                      | status_from / status_to              |
                      | changed_by                           |
                      +--------------------------------------+
```

---

## ⚙️ Setup & Local Installation

1. **Clone repository and install dependencies:**
   ```bash
   git clone https://github.com/your-username/clinic-management-api.git
   cd clinic-management-api
   composer install
   ```

2. **Configure environment credentials:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Set your PostgreSQL configuration in `.env`:**
   ```env
   APP_TIMEZONE=Asia/Tehran

   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=clinic_management_db
   DB_USERNAME=postgres
   DB_PASSWORD=your_password

   QUEUE_CONNECTION=database
   MAIL_MAILER=log
   ```

4. **Execute database migrations & seed default data:**
   ```bash
   php artisan migrate --seed
   ```
   * *Default Seeded Admin Credentials:* `admin@example.com` / `adminPassword123`

5. **Start HTTP server, background queue worker, and task scheduler:**
   * **Terminal 1 (API Server):**
     ```bash
     php artisan serve
     ```
   * **Terminal 2 (Queue Worker for Notifications):**
     ```bash
     php artisan queue:work
     ```
   * **Terminal 3 (Scheduler for Reminders):**
     ```bash
     php artisan schedule:work
     ```

---
