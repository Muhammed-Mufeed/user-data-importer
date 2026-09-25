# 🚀 User Data Importer

<div align="center">

<p align="center">
  <strong>A modern, beginner-friendly, and fault-tolerant user ingestion system.</strong><br>
  Clean, validate, and import CSV user datasets into PostgreSQL with zero hassle — via a standalone <strong>Terminal CLI tool</strong> or a sleek <strong>React Web Dashboard</strong>!
</p>

[Quick Start](#quick-start) •
[Tech Stack](#tech-stack) •
[How It Works](#how-it-works) •
[CLI Usage](#cli-usage) •
[Web Dashboard](#web-dashboard) •
[Validation Rules](#validation-rules) •
[API Docs](#api-docs) •
[Testing](#testing) •
[Troubleshooting](#troubleshooting)

</div>

---

## 📖 Table of Contents

- [🌟 What Is This Project?](#what-is-this-project)
- [🛠️ Tech Stack](#tech-stack)
- [🧠 How It Works](#how-it-works)
- [📂 Project Structure](#project-structure)
- [⚡ Quick Start (Choose Your Path)](#quick-start)
  - [🐳 Method 1: Docker (Fastest & Recommended)](#docker-setup)
  - [💻 Method 2: Native Local Setup](#native-setup)
- [⌨️ Command-Line Interface (`user_upload.php`)](#cli-usage)
  - [CLI Directives Table](#cli-directives)
  - [Ready-to-Run CLI Examples](#cli-examples)
  - [Sample Terminal Output](#cli-output)
- [🖥️ Interactive Web Dashboard](#web-dashboard)
- [🧹 Data Cleaning & Validation Rules](#validation-rules)
- [🗄️ Database Architecture](#database-architecture)
- [🌐 REST API Documentation](#api-docs)
- [🧪 Automated Testing](#testing)
- [🏗️ Software Architecture & Design Patterns](#architecture-patterns)
- [❓ Troubleshooting & FAQ](#troubleshooting)

---

<a id="what-is-this-project"></a>
## 🌟 What Is This Project?

Imagine you run an organization or web app and receive a messy CSV spreadsheet containing hundreds of users. The spreadsheet has typos, random capitalizations (`jOhN`, `sMiTh`), invalid emails (`user@@example`), blank fields, and duplicate entries. 

If you try to insert that data directly into a database, queries break, users get locked out, and your system crashes.

**User Data Importer solves this problem completely.** It is a complete pipeline that:
1. **Reads** any CSV file with user data.
2. **Cleans & Sanitizes** names and emails automatically.
3. **Validates** each row with strict checks.
4. **Isolates errors** without stopping the import (valid rows are saved, bad rows are reported with clear reasons).
5. **Prevents duplicates** both within the file and against existing database records.
6. **Offers two interfaces**: A terminal CLI script for automated jobs and a modern React dashboard for visual users.

> [!NOTE]
> **Zero Code Duplication (DRY):** Both the terminal command-line tool and the web dashboard execute the **exact same PHP business logic under the hood**.

---

<a id="tech-stack"></a>
## 🛠️ Tech Stack

| Category | Technology | Version | Purpose in this Project |
|---|---|---|---|
| **Backend Engine** | PHP | `8.3+` | Handles CSV parsing, string normalization, validation, and CLI commands with strict types (`declare(strict_types=1)`). |
| **Database** | PostgreSQL (PDO) | `16+` | Relational persistence with unique constraints on email, atomic transactions, and prepared statements. |
| **Frontend Framework** | React | `19.x` | Modern, responsive web dashboard with live dry-run previews, filterable records, and modal views. |
| **Language (Frontend)** | TypeScript | `5.x` | Type safety across components, props, hooks, and API communication models. |
| **Styling & UI** | Tailwind CSS | `4.x` | Responsive dark theme styling, utility classes, and custom glassmorphic components. |
| **Build Tool & Bundler** | Vite | `6.x` | Lightning-fast frontend development server and production bundler with Hot Module Replacement (HMR). |
| **Dependency Manager** | Composer | `2.x` | Manages PHP packages and provides PSR-4 autoloading (`App\` $\rightarrow$ `src/`, `App\Tests\` $\rightarrow$ `tests/`). |
| **Testing Suite** | PHPUnit | `11.x` | Comprehensive unit and integration test suites with in-memory repository doubles. |
| **Containerization** | Docker & Compose | Multi-arch | One-command multi-container orchestration (PostgreSQL + PHP API + React). |

---

<a id="how-it-works"></a>
## 🧠 How It Works 

Here is a visual map of how data travels through the system:

```text
 ┌─────────────────────────┐               ┌─────────────────────────┐
 │   Terminal User (CLI)   │               │   Browser User (Web)    │
 │   php user_upload.php   │               │   React 19 Dashboard    │
 └────────────┬────────────┘               └────────────┬────────────┘
              │                                         │
              │                                         ▼
              │                            ┌─────────────────────────┐
              │                            │     REST API Gateway    │
              │                            │    backend/public/      │
              │                            └────────────┬────────────┘
              │                                         │
              └───────────────────┬─────────────────────┘
                                  ▼
 ┌──────────────────────────────────────────────────────────────────┐
 │                    CORE INGESTION ENGINE (PHP)                   │
 │                                                                  │
 │  1. CsvParser       ──► Streams file row-by-row (memory-safe)    │
 │  2. UserValidator   ──► Trims, capitalizes names & checks emails │
 │  3. UserImporter    ──► Sorts into Valid vs Invalid records      │
 └────────────────────────────────┬─────────────────────────────────┘
                                  ▼
 ┌──────────────────────────────────────────────────────────────────┐
 │                     PERSISTENCE LAYER (PDO)                      │
 │                                                                  │
 │  • Dry Run?         ──► 0 database changes (Simulation mode)     │
 │  • Live Import?     ──► Atomic Batch Insert into PostgreSQL      │
 └────────────────────────────────┬─────────────────────────────────┘
                                  ▼
 ┌──────────────────────────────────────────────────────────────────┐
 │                      POSTGRESQL 16 DATABASE                      │
 │                      Table: 'users'                              │
 └──────────────────────────────────────────────────────────────────┘
```

---

<a id="project-structure"></a>
## 📂 Project Structure

Here is how the project repository is organized:

```text
user-data-importer/
│
├── user_upload.php          # 🚀 Root CLI entry point (forwards to backend)
├── docker-compose.yml       # 🐳 1-click Docker environment (DB + API + Frontend)
├── README.md                # 📖 You are here!
│
├── data/                    # 📁 Sample datasets
│   └── users.csv            # 49-row test dataset (contains both valid & edge-case rows)
│
├── backend/                 # 🐘 PHP 8.3 Backend & CLI Engine
│   ├── composer.json        # PHP dependencies and PSR-4 autoloader setup
│   ├── phpunit.xml          # Automated test configuration
│   ├── user_upload.php      # Main backend CLI script
│   │
│   ├── config/              # Configuration files
│   │   └── database.php     # PostgreSQL connection parameters
│   │
│   ├── public/              # Web Server Document Root
│   │   └── index.php        # JSON REST API router (CORS-enabled)
│   │
│   ├── src/                 # 🧩 Clean OOP Source Code
│   │   ├── CLI/             # Command-line runner & argument parser
│   │   │   └── CliCommand.php
│   │   ├── Database/        # PDO Singleton connection manager
│   │   │   └── Connection.php
│   │   ├── Models/          # Data Transfer Object (User entity)
│   │   │   └── User.php
│   │   ├── Repositories/    # Database queries & table management
│   │   │   ├── UserRepository.php
│   │   │   └── UserRepositoryInterface.php
│   │   └── Services/        # Core business rules & validation
│   │       ├── CsvParser.php      # Memory-safe file stream reader
│   │       ├── UserValidator.php  # Name capitalization & email rules
│   │       └── UserImporter.php   # Pipeline coordinator
│   │
│   └── tests/               # 🧪 Automated Test Suites
│       ├── Unit/            # Fast, isolated tests for Parser & Validator
│       └── Integration/     # Full import tests with mock database
│
└── frontend/                # ⚛️ React 19 + TypeScript Web App
    ├── package.json         # Node.js dependencies (Vite, Lucide icons, etc.)
    ├── vite.config.ts       # Vite bundler configuration
    ├── index.html           # HTML template
    └── src/
        ├── App.tsx          # Main application page
        ├── components/      # Modular UI components
        │   ├── Header.tsx             # Navbar with API status indicator
        │   ├── FileUpload.tsx         # Drag-and-drop zone + 1-click sample button
        │   ├── StatsSummary.tsx       # Live counters (Total, Valid, Rejected)
        │   ├── PreviewTable.tsx       # Interactive table with search & tabs
        │   ├── ImportAction.tsx       # Dry-run vs Commit controls
        │   └── DatabaseViewerModal.tsx# Live PostgreSQL table explorer
        └── services/        # Frontend API client
            └── api.ts
```

---

<a id="quick-start"></a>
## ⚡ Quick Start (Choose Your Path)

Choose the setup method that works best for you:
- **[Method 1: Docker](#-method-1-docker-compose-fastest--recommended)** — **Recommended for beginners.** Requires only Docker Desktop; no local PHP, Node, or PostgreSQL setup needed.
- **[Method 2: Native Local Setup](#-method-2-native-local-setup)** — Best if you want to run PHP, Node, and PostgreSQL directly on your host machine.

---

<a id="docker-setup"></a>
### 🐳 Method 1: Docker Compose (Fastest & Recommended)

#### Step 1: Clone and Enter the Directory
```bash
git clone https://github.com/Muhammed-Mufeed/user-data-importer.git
cd user-data-importer
```

#### Step 2: Start All Services with One Command
```bash
docker compose up --build
```

That's it! Docker spins up 3 coordinated containers:
- 🗄️ **PostgreSQL 16**: `localhost:5432`
- 🐘 **PHP 8.3 REST API**: `http://localhost:8000/api/health`
- ⚛️ **React 19 Dashboard**: `http://localhost:5173`

#### Step 3: Open the Dashboard
Open your browser and visit:  
👉 **[http://localhost:5173](http://localhost:5173)**

#### Run CLI Inside the Docker Container
Want to test the CLI tool while Docker is running? Run:
```bash
# View CLI help manual
docker exec -it user_importer_backend php user_upload.php --help

# Create the database table
docker exec -it user_importer_backend php user_upload.php --create_table

# Dry-run test dataset
docker exec -it user_importer_backend php user_upload.php --file data/users.csv --dry_run

# Import test dataset into PostgreSQL
docker exec -it user_importer_backend php user_upload.php --file data/users.csv
```

#### Stopping the Containers
To stop the services, press `Ctrl + C` in your terminal or run:
```bash
docker compose down
```

---

<a id="native-setup"></a>
### 💻 Method 2: Native Local Setup

#### Prerequisites
Make sure you have installed on your computer:
* **PHP 8.3 or higher** with `pdo_pgsql` extension enabled (`php -v`)
* **Composer 2.x** (`composer -v`)
* **Node.js 18+ & npm** (`node -v`, `npm -v`)
* **PostgreSQL 16+** service running locally (`psql --version`)

---

#### Step 1: Set Up the PostgreSQL Database
Create a database named `user_importer` in your local PostgreSQL:
```sql
CREATE DATABASE user_importer;
```

---

#### Step 2: Configure Backend Environment Variables
Create your `.env` file from the example template:

*On Linux / macOS / Git Bash:*
```bash
cp backend/.env.example backend/.env
```

*On Windows (PowerShell):*
```powershell
Copy-Item backend/.env.example backend/.env
```

Open `backend/.env` in your text editor and ensure credentials match your local PostgreSQL server:
```ini
DB_HOST=localhost
DB_PORT=5432
DB_NAME=user_importer
DB_USER=postgres
DB_PASSWORD=your_postgres_password
```

---

#### Step 3: Install Backend Dependencies & Create Table
```bash
cd backend
composer install
cd ..

# Initialize the 'users' table using the CLI tool:
php user_upload.php --create_table
```

---

#### Step 4: Start the Backend API Server
```bash
php -S localhost:8000 -t backend/public
```
> [!TIP]
> Keep this terminal open! Test it by opening `http://localhost:8000/api/health` in your browser. You should see `{"success": true, "message": "..."}`.

---

#### Step 5: Start the React Frontend Dashboard
Open a **new terminal tab or window**:
```bash
cd frontend
npm install
npm run dev
```

Your React app is now live! Open your browser at:  
👉 **[http://localhost:5173](http://localhost:5173)**

---

<a id="cli-usage"></a>
## ⌨️ Command-Line Interface (`user_upload.php`)

The CLI script is located right at the project root for maximum convenience:

```bash
php user_upload.php [DIRECTIVES]
```

<a id="cli-directives"></a>
### CLI Directives Table

| Directive | Type | Purpose | Example |
|---|---|---|---|
| `--file <path>` | Required for Import | Specifies the CSV file to parse, validate, and import. | `--file data/users.csv` |
| `--create_table` | Action Flag | Builds the PostgreSQL `users` table schema and exits. | `--create_table` |
| `--dry_run` | Modifier Flag | Simulates parsing & validation **without writing** to the database. | `--dry_run` |
| `-u <user>` | DB Override | Overrides PostgreSQL username for this run. | `-u postgres` |
| `-p <pass>` | DB Override | Overrides PostgreSQL password for this run. | `-p secret123` |
| `-h <host>` | DB Override | Overrides PostgreSQL host address for this run. | `-h localhost` |
| `-d <dbname>` | DB Override | Overrides PostgreSQL database name for this run. | `-d user_importer` |
| `--help` | Informational | Displays the interactive manual and usage guide. | `--help` |

---

<a id="cli-examples"></a>
### Ready-to-Run CLI Examples

```bash
# 1. View all available commands and help documentation:
php user_upload.php --help

# 2. Initialize the PostgreSQL table (creates table if not already present):
php user_upload.php --create_table

# 3. Perform a safe DRY RUN (checks all rows, reports errors, changes NOTHING):
php user_upload.php --file data/users.csv --dry_run

# 4. Perform a real import into the database:
php user_upload.php --file data/users.csv

# 5. Import using custom database credentials from terminal:
php user_upload.php --file data/users.csv -h localhost -d user_importer -u postgres -p mypassword
```

---

<a id="cli-output"></a>
### Sample Terminal Output

When you run `php user_upload.php --file data/users.csv`, here is the formatted report you will see:

```text
Parsing and processing CSV: data/users.csv...

========================================================
                 IMPORT SUMMARY REPORT                  
========================================================
  Total Rows Processed : 49
  Valid Records        : 41
  Invalid Records      : 8
  Rows Inserted to DB  : 41
========================================================

--------------------------------------------------------
                 REJECTED ROWS & ERRORS                 
--------------------------------------------------------
  [Line 42] 'invalid email' (invalid-email) -> Invalid email format: 'invalid-email'.
  [Line 43] 'missing domain' (missing@) -> Invalid email format: 'missing@'.
  [Line 44] 'duplicate user' (john.smith@example.com) -> Duplicate email in CSV batch: 'john.smith@example.com'.
  [Line 45] 'another duplicate' (JOHN.SMITH@EXAMPLE.COM) -> Duplicate email in CSV batch: 'john.smith@example.com'.
  [Line 46] '<empty> noname' (noname@example.com) -> Missing or empty name field.
  [Line 47] 'noname <empty>' (missing.surname@example.com) -> Missing or empty surname field.
  [Line 48] 'missing email' (<empty>) -> Missing or empty email field.
  [Line 49] 'bad format' (bad@@example.com) -> Invalid email format: 'bad@@example.com'.
--------------------------------------------------------
```

---

<a id="web-dashboard"></a>
## 🖥️ Interactive Web Dashboard

If you prefer a visual experience, the web application (`http://localhost:5173`) provides an intuitive workflow:

```text
Step 1: Upload or Click "Try Sample Data"
   │
   ▼
Step 2: Instant Dry-Run Preview (Calculates metrics with ZERO database writes)
   │
   ▼
Step 3: Filter & Inspect (Tabs for All, Valid Only, Rejected Only + Search)
   │
   ▼
Step 4: Commit Valid Records (One-click atomic transactional insert)
   │
   ▼
Step 5: View Live Database (Open the live database modal to verify stored records)
```

### Key UI Features:
1. **1-Click Sample Data Demo**: No file on hand? Click the **"Try Sample Data"** button to instantly load the bundled 49-row dataset.
2. **Instant Dry-Run Preview**: Dropping a file runs an in-memory validation pass. It displays live counters for **Total Rows**, **Valid Count**, and **Invalid Count**.
3. **Interactive Inspection Table**:
   - Filter rows using tabs: **All**, **Valid Only (Green)**, or **Errors Only (Red)**.
   - Real-time search filter by name, surname, or email.
   - Hover over error badges to see the exact rejection reason.
4. **Safe Commit Button**: Valid records can be committed to PostgreSQL with a single click. The UI displays exactly how many records were inserted.
5. **Live Database Viewer**: Click **"View Database"** in the top navigation to inspect records currently residing in PostgreSQL without leaving your browser.

---

<a id="validation-rules"></a>
## 🧹 Data Cleaning & Validation Rules

Every row in the uploaded CSV is processed through strict sanitization and validation:

| Field | Input Example | Cleaned Output | Rule Description |
|---|---|---|---|
| **Name** | `  jOhN  ` | `John` | Strips extra whitespace, forces title casing (`ucfirst(strtolower())`). Cannot be blank. |
| **Surname** | `o'CONNOR` | `O'connor` | Strips extra whitespace, title-cases the surname. Cannot be blank. |
| **Email** | `JOHN.DOE@GMAIL.COM` | `john.doe@gmail.com` | Strips whitespace, converts to lowercase, validates RFC 5322 compliance. |

### Rejection Conditions:
A row is flagged as **Invalid** and rejected if:
- ❌ **Missing Value**: The `name`, `surname`, or `email` column is empty or whitespace-only.
- ❌ **Malformed Email**: The email does not match a valid format (e.g. `user@`, `bad@@domain.com`, `user.domain.com`).
- ❌ **Duplicate Email in CSV**: If the same email appears multiple times in the uploaded CSV, only the first occurrence is considered valid; duplicates are rejected.
- ❌ **Existing Email in Database**: If an email is already stored in the PostgreSQL database, it is safely skipped during insertion thanks to `ON CONFLICT (email) DO NOTHING`.

---

<a id="database-architecture"></a>
## 🗄️ Database Architecture

The PostgreSQL database structure is intentionally lean and optimized:

```sql
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL
);
```

### Safety & Integrity Guarantees:
1. **Strict Unique Constraint**: The `UNIQUE` index on the `email` column guarantees that no two records in PostgreSQL can ever share the same email address.
2. **Idempotent Batch Insertion**: Insertion queries use `INSERT INTO users (...) VALUES (...) ON CONFLICT (email) DO NOTHING`. Re-importing a CSV multiple times is completely safe and will never duplicate records or crash.
3. **Prepared Statements**: 100% of database interactions use parameterized PDO statements, providing absolute protection against SQL Injection attacks.
4. **Atomic Transactions**: Multi-row inserts are wrapped in `BEGIN ... COMMIT` blocks. If a connection error occurs halfway through, the transaction rolls back cleanly.

---

<a id="api-docs"></a>
## 🌐 REST API Documentation

The backend exposes a lightweight, CORS-ready JSON REST API running on port `8000`:

| Method | Endpoint | Description | Expected Body / Form Data |
|---|---|---|---|
| `GET` | `/api/health` | Service health status check | None |
| `POST` | `/api/create-table` | Creates the `users` table if missing | None |
| `POST` | `/api/validate` | Dry-run validation of a CSV file | `multipart/form-data` with `file: <.csv>` |
| `POST` | `/api/import` | Validates and imports valid rows to DB | `multipart/form-data` with `file: <.csv>` |
| `GET` | `/api/users` | Returns list of all stored users | None |

### Example API Response (`POST /api/validate`):
```json
{
  "success": true,
  "filename": "users.csv",
  "total_rows": 49,
  "valid_count": 41,
  "invalid_count": 8,
  "is_dry_run": true,
  "records": [
    {
      "valid": true,
      "user": {
        "name": "John",
        "surname": "Smith",
        "email": "john.smith@example.com"
      },
      "error": null,
      "row": 2
    },
    {
      "valid": false,
      "user": null,
      "error": "Invalid email format: 'missing@'.",
      "row": 43
    }
  ]
}
```

---

<a id="testing"></a>
## 🧪 Automated Testing

The project includes an automated test suite powered by **PHPUnit 11**, covering both isolated unit tests and end-to-end integration tests.

### Running the Tests:
```bash
cd backend
php vendor/phpunit/phpunit/phpunit --testdox
```

### Test Suite Summary (12 Tests, 57 Assertions — 100% Passing):

```text
Csv Parser (App\Tests\Unit\CsvParser)
 ✔ Parses valid csv file
 ✔ Throws exception for non existent file
 ✔ Throws exception when required headers are missing
 ✔ Skips empty lines

User Validator (App\Tests\Unit\UserValidator)
 ✔ Capitalizes names properly
 ✔ Lowercases emails
 ✔ Rejects missing fields
 ✔ Rejects invalid email formats
 ✔ Rejects duplicate emails in batch

User Importer (App\Tests\Integration\UserImporter)
 ✔ Dry run does not insert into database
 ✔ Imports only valid records
 ✔ Accurately processes challenge dataset (41 Valid / 8 Invalid)

OK (12 tests, 57 assertions)
```

> [!NOTE]
> Tests utilize an in-memory repository mock (`UserRepositoryInterface`), meaning the full test suite runs in under **0.05 seconds** without needing a live PostgreSQL database!

---

<a id="architecture-patterns"></a>
## 🏗️ Software Architecture & Design Patterns

The codebase adheres strictly to modern software engineering best practices:

### 1. Separation of Concerns & Repository Pattern
- **Business Logic (`src/Services/`)**: Handles file streaming (`CsvParser`), normalization & regex validation (`UserValidator`), and orchestrating the flow (`UserImporter`). These classes contain **zero raw SQL queries**.
- **Data Access (`src/Repositories/`)**: `UserRepository` encapsulates all database operations. If you ever switch from PostgreSQL to MySQL or SQLite, you only change the repository class — the business logic remains untouched.

### 2. SOLID Principles in Practice
- **Single Responsibility Principle (SRP)**: Each class has one job (`CsvParser` parses files, `UserValidator` checks rules, `UserRepository` communicates with PostgreSQL).
- **Open/Closed Principle (OCP)**: Services can be extended or injected with new implementations without modifying existing source code.
- **Liskov Substitution Principle (LSP)**: `UserImporter` accepts any class implementing `UserRepositoryInterface` (whether a live database or an in-memory test double).
- **Interface Segregation Principle (ISP)**: `UserRepositoryInterface` specifies only the minimal necessary methods (`createTable`, `insertUsersBatch`, `emailExists`).
- **Dependency Inversion Principle (DIP)**: High-level modules depend on abstractions (interfaces) rather than concrete implementations.

### 3. Memory-Safe Streaming
Instead of loading an entire CSV into RAM with `file_get_contents()`, `CsvParser` uses stream pointers (`fopen` and `fgetcsv`). This guarantees **constant memory usage ($O(1)$)**, allowing the engine to process gigabyte-sized CSV files with millions of rows without memory exhaustion.

---

<a id="troubleshooting"></a>
## ❓ Troubleshooting & FAQ

<details>
<summary><strong>Q: "Port 5432 is already in use" error when starting Docker.</strong></summary>

**Cause:** You likely have a local instance of PostgreSQL already running on your computer.  
**Fix:** Either stop your local PostgreSQL service (`sudo service postgresql stop` on Linux, or stop the service in `services.msc` on Windows), OR change the mapped port in `docker-compose.yml` from `"5432:5432"` to `"5433:5432"`.
</details>

<details>
<summary><strong>Q: "could not find driver" or "PDO pgsql driver missing" in local PHP.</strong></summary>

**Cause:** Your PHP installation does not have the PostgreSQL PDO extension enabled.  
**Fix:**
1. Open your `php.ini` file (run `php --ini` to locate it).
2. Find `;extension=pdo_pgsql` and remove the leading semicolon (`;`) so it reads `extension=pdo_pgsql`.
3. Find `;extension=pgsql` and remove the semicolon.
4. Save the file and restart your terminal.
</details>

<details>
<summary><strong>Q: Why does the dataset report 41 valid rows and 8 invalid rows?</strong></summary>

**Explanation:** The bundled challenge dataset (`data/users.csv`) contains 49 rows in total:
- Line 1: Header row (`name, surname, email`).
- Lines 2–41: 41 well-formed user records.
- Lines 42–49: 8 intentional edge-case rows (broken emails like `missing@`, missing names, and duplicate emails) designed to thoroughly test the validation engine.
</details>

<details>
<summary><strong>Q: What happens if I import the same CSV file twice?</strong></summary>

**Answer:** Nothing will break! The import engine utilizes PostgreSQL's `ON CONFLICT (email) DO NOTHING` directive. Any user whose email already exists in the database will simply be skipped, preventing duplicates and ensuring data integrity.
</details>

---
