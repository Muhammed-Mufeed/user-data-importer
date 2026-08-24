# User Data Importer 

A production-grade user data ingestion engine featuring a shared **PHP 8.3 OOP core**, transactional **PostgreSQL persistence via native PDO**, a standalone **CLI tool (`user_upload.php`)**, and an interactive **React web dashboard**.

---

## 🏗️ Architecture & System Overview

Both the Command Line Interface and the React Web Dashboard execute the **exact same core business engine** (Zero Code Duplication / DRY):

```text
┌───────────────────────────────────────────────────────────────────────────┐
│                                ENTRY POINTS                               │
│   CLI Tool (user_upload.php)          │    React Web Dashboard (Vite)     │
│   Terminal flags & directives         │    Drag-and-drop & live preview   │
└─────────────────────┬─────────────────┴─────────────────────┬─────────────┘
                      │                                       │
                      │                                       ▼
                      │                            REST API Gateway (public/)
                      │                            CORS & JSON HTTP Endpoints
                      │                                       │
                      └───────────────────┬───────────────────┘
                                          ▼
                ┌───────────────────────────────────────────────────┐
                │        SHARED CORE BUSINESS ENGINE (src/)         │
                │                                                   │
                │   CsvParser          UserValidator   UserImporter │
                │   (Memory Streaming) (Sanitizer)     (Orchestr.)  │
                └─────────────────────────┬─────────────────────────┘
                                          ▼
                ┌───────────────────────────────────────────────────┐
                │             PERSISTENCE LAYER (src/)              │
                │                                                   │
                │   UserRepositoryInterface ◄── UserRepository      │
                │   Connection (PDO Singleton)                      │
                └─────────────────────────┬─────────────────────────┘
                                          ▼
                ┌───────────────────────────────────────────────────┐
                │               POSTGRESQL 16+ DATABASE             │
                │   Table: users (unique constraint on email)       │
                └───────────────────────────────────────────────────┘
```

---

## 🛠️ System Requirements & Tech Stack

| Component | Technology | Version | Key Standard / Role |
|---|---|---|---|
| **Backend Runtime** | PHP | `8.3+` | `declare(strict_types=1);`, Constructor Promotion, Strict Types |
| **Package Manager** | Composer | `2.x+` | PSR-4 Autoloading (`App\` $\rightarrow$ `src/`, `App\Tests\` $\rightarrow$ `tests/`) |
| **Database Layer** | PostgreSQL (PDO) | `16+` | 100% Prepared Statements, Atomic Transactions, Unique Constraints |
| **Testing Engine** | PHPUnit | `11.x+` | Isolated Unit Test Suites & Full Pipeline Integration Suites |
| **Frontend Client** | React + TypeScript | `19.x` | Single Page Application (SPA), Vite Bundler |
| **Styling System** | Tailwind CSS | `4.x` | Responsive Dark Theme & Glassmorphic Component UI |
| **Containerization**| Docker & Docker Compose | Multi-arch | Multi-container Environment (PostgreSQL + PHP API + React) |

---

## 🚀 Quick Start Guide

To make evaluation as simple and flexible as possible, the project supports two setup methods:
* If you have Docker installed, **Option A** allows you to spin up the entire multi-container stack with a single command without needing to configure local PHP, Composer, or PostgreSQL.
* Alternatively, you can run everything natively on your machine via **Option B**.

---

### 🐳 Option A: Docker Compose Setup

Run PostgreSQL 16, the PHP 8.3 REST API/CLI, and the React 19 Frontend with a single command:

```bash
docker compose up --build
```

* **React Web Dashboard**: `http://localhost:5173`
* **PHP REST API Gateway**: `http://localhost:8000/api/health`
* **PostgreSQL Database**: `localhost:5432` (`user_importer`)

---

### 💻 Option B: Native Local Setup

#### Step 1: Configure Environment Variables
```bash
cp backend/.env.example backend/.env
```
Update `backend/.env` with your local PostgreSQL credentials:
```ini
DB_HOST=localhost
DB_PORT=5432
DB_NAME=user_importer
DB_USER=postgres
DB_PASSWORD=your_password
```

#### Step 2: Install Backend Dependencies
```bash
cd backend
composer install
cd ..
```

#### Step 3: Start the Backend REST API Server
```bash
php -S localhost:8000 -t backend/public
```

#### Step 4: Start the Frontend React Client (in a second terminal)
```bash
cd frontend
npm install
npm run dev
```
Open **`http://localhost:5173`** in your browser.

---

## ⌨️ CLI Directives Specification (`user_upload.php`)

The standalone command-line tool executes directly from the project root:

```bash
php user_upload.php [DIRECTIVES]
```

### Directives Reference Table:

| Directive | Type | Description |
|---|---|---|
| `--file <csv_file>` | Required for Import | The path to the CSV file to be parsed, validated, and imported. |
| `--create_table` | Standalone Action | Builds and verifies the PostgreSQL `users` table schema and exits. |
| `--dry_run` | Modifier Flag | Executes parsing and validation without altering the database (Simulation Mode). |
| `-u <username>` | DB Credential | Override PostgreSQL database username. |
| `-p <password>` | DB Credential | Override PostgreSQL database password. |
| `-h <host>` | DB Credential | Override PostgreSQL database host. |
| `-d <database>` | DB Credential | Override PostgreSQL database name. |
| `--help` | Informational | Displays directive usage instructions and example commands. |

### CLI Example Commands:

```bash
# 1. Display help manual:
php user_upload.php --help

# 2. Initialize PostgreSQL table:
php user_upload.php --create_table

# 3. Execute Dry-Run simulation on dataset:
php user_upload.php --file data/users.csv --dry_run

# 4. Execute live database import:
php user_upload.php --file data/users.csv

# 5. Execute import with custom database credentials:
php user_upload.php --file data/users.csv -h localhost -d user_importer -u postgres -p secret
```

### Sample CLI Terminal Output:

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

## 🖥️ Interactive Web Dashboard (React 19)

The web client provides a safe, 2-step verification workflow:

1. **Step 1: File Upload & Validation**: Drag and drop `.csv` files (or click "Try Sample Data" for a 1-click demo).
2. **Step 2: Instant Dry-Run Preview**: Automatically parses the file in memory (0 database writes) and calculates real-time metrics: Total Rows, Valid Count, Invalid Count.
3. **Step 3: Interactive Record Inspection**: Filter rows by `All`, `Valid`, or `Errors` tabs, search in real-time, and inspect red hover tooltips explaining why malformed rows were flagged.
4. **Step 4: Transactional Import Action**: Commit valid records to PostgreSQL with dynamic feedback (distinguishing newly inserted rows from pre-existing records).
5. **Step 5: Live Database Modal**: View live rows directly from the PostgreSQL `users` table without leaving the browser.

---

## 🗄️ Database Schema & Persistence Guarantees

```sql
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL
);
```

1. **Strict Unique Constraint**: Enforces data integrity at the database engine level via `UNIQUE NOT NULL` on `email`.
2. **Idempotent Batch Inserts**: Uses `ON CONFLICT (email) DO NOTHING` so that re-running imports safely skips duplicate users without throwing fatal SQL errors.
3. **Atomic Transactions**: All database operations execute inside transactions (`BEGIN` / `COMMIT`), ensuring automatic rollback if any connection interruption occurs.

---

## 🧪 Automated Testing with PHPUnit

The project contains a comprehensive automated test suite designed with high isolation and regression safety:

```bash
cd backend
php vendor/phpunit/phpunit/phpunit --testdox
```

### Why Automated Testing Matters in this Pipeline:
* **Unit Testing (`tests/Unit/`)**: Isolates parsing and validation from external infrastructure. Verifies edge cases (e.g. whitespace trimming, casing normalization, Unicode characters, and malformed emails) in sub-millisecond execution times.
* **Integration Testing (`tests/Integration/`)**: Verifies the end-to-end orchestration pipeline (`CsvParser` $\rightarrow$ `UserValidator` $\rightarrow$ `UserRepositoryInterface`). Guarantees that `--dry_run` makes 0 database calls and confirms that the challenge dataset (`data/users.csv`) produces exactly **41 valid** and **8 invalid** rows.
* **Decoupled Test Doubles**: Tests use lightweight in-memory implementations of `UserRepositoryInterface`, allowing the full test suite to run in 0.03 seconds without requiring a live PostgreSQL instance.

### Test Results (12 Tests, 57 Assertions — 100% Green):

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

---

## 💡 Architectural Decisions, SOLID Principles & Design Patterns

### 1. The Service-Repository Pattern
Separating business logic from persistence logic is fundamental to building scalable, maintainable software:
* **Business Services Layer (`src/Services/`)**: Focuses entirely on data processing. `CsvParser` handles file streaming, `UserValidator` applies sanitization and RFC rules, and `UserImporter` coordinates the workflow. These classes have **no knowledge of raw SQL**.
* **Repository Layer (`src/Repositories/`)**: `UserRepository` encapsulates all PDO database interactions, table creation, and parameterized SQL queries.
* **Benefit**: We can swap PostgreSQL for MySQL, SQLite, or an in-memory test store without touching a single line of parsing or validation logic!

### 2. SOLID Principles Implementation
* **Single Responsibility Principle (SRP)**:
  * `CsvParser.php`: Exclusively handles stream pointers, header checks, and row extraction.
  * `UserValidator.php`: Exclusively enforces normalization rules (`ucfirst(strtolower(trim()))`), RFC email validation, and in-batch duplicate tracking.
  * `UserRepository.php`: Exclusively manages database connection lifecycle and SQL execution.
  * `UserImporter.php`: Exclusively orchestrates the pipeline workflow.
* **Open/Closed Principle (OCP)**:
  * Core services are open for extension through dependency injection without modifying existing implementations.
* **Liskov Substitution Principle (LSP)**:
  * `UserImporter` accepts any class conforming to `UserRepositoryInterface` (e.g. live database repository or lightweight in-memory mock).
* **Interface Segregation Principle (ISP)**:
  * `UserRepositoryInterface` is lean and focused (`createTable`, `insertUsersBatch`, `emailExists`) without forcing unnecessary dependencies.
* **Dependency Inversion Principle (DIP)**:
  * High-level orchestrators (`UserImporter`, `CliCommand`) depend on abstractions (`UserRepositoryInterface`) rather than concrete database connections.

### 3. Additional Design Highlights
* **Memory-Safe Streaming**: Uses native `fopen` and `fgetcsv` stream pointers, allowing the application to process gigabyte-sized CSV files with constant memory usage.
* **Defensive Fault-Tolerance**: Invalid rows are trapped, recorded with specific error messages, and skipped, allowing valid records to proceed.
* **Zero External CLI Dependencies**: Native argument parsing without heavy third-party packages keeps the script lightweight, fast, and easy to maintain.