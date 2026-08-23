# User Data Importer (PHP 8.3 + PostgreSQL + React)

A production-grade, high-performance user data import and processing engine featuring a shared PHP 8.3 OOP core, transactional PostgreSQL persistence via native PDO, a standalone CLI utility, and an interactive React web dashboard.

---

## 🏗️ Architecture & Engineering Highlights

```text
┌────────────────────────────────────────────────────────────────────────┐
│                          ENTRY POINTS                                  │
│   CLI Tool (user_upload.php)       │    React Web Dashboard (Vite)     │
│   Terminal flags & directives      │    Drag-and-drop & live preview   │
└──────────────────┬─────────────────┴───────────────────┬───────────────┘
                   │                                     │
                   │                                     ▼
                   │                           REST API Gateway (public/)
                   │                           CORS & JSON HTTP Endpoints
                   │                                     │
                   └──────────────────┬──────────────────┘
                                      ▼
             ┌─────────────────────────────────────────────────┐
             │       SHARED CORE BUSINESS ENGINE (src/)        │
             │                                                 │
             │   CsvParser        UserValidator   UserImporter │
             │   (Streaming)      (Clean & Rule)  (Orchestr.)  │
             └────────────────────────┬────────────────────────┘
                                      ▼
             ┌─────────────────────────────────────────────────┐
             │            PERSISTENCE LAYER (src/)             │
             │                                                 │
             │   UserRepositoryInterface ◄── UserRepository    │
             │   Connection (PDO Singleton)                    │
             └────────────────────────┬────────────────────────┘
                                      ▼
             ┌─────────────────────────────────────────────────┐
             │              POSTGRESQL 16+ DATABASE            │
             │   Table: users (unique constraint on email)     │
             └─────────────────────────────────────────────────┘
```

- **Zero Code Duplication (DRY)**: Identical validation rules, parsing algorithms, and persistence logic shared between the CLI tool and the React Web API.
- **Strict Typing & Modern OOP**: 100% `declare(strict_types=1);`, Constructor Property Promotion, and explicit return types in PHP 8.3.
- **Secure Persistence**: 100% PDO prepared statements with native parameter binding (SQL-injection proof) and atomic transactions (`BEGIN`/`COMMIT`/`ROLLBACK`).
- **Dependency Inversion (SOLID)**: Repositories implement explicit interface contracts and receive database connections via constructor injection.

---

## 🛠️ System Requirements & Tech Stack

### Backend Environment
- **PHP**: `8.3+` (Extensions: `pdo_pgsql`, `pgsql`, `zip`, `curl`, `mbstring`, `openssl`)
- **Package Manager**: **Composer** `2.x+` (PSR-4 Autoloading)
- **Database Engine**: **PostgreSQL** `16+`
- **Testing Framework**: **PHPUnit** `11.x+`

### Frontend Client
- **Framework**: **React 18+** (Single Page Application)
- **Build Tool**: **Vite**
- **Styling**: **Tailwind CSS**
- **Runtime / Bundler Environment**: **Node.js** `18+` / `npm` or `pnpm`

---

## 📂 Repository Directory Layout

```text
user-data-importer/
├── backend/
│   ├── config/
│   │   └── database.php           # Database configuration settings & .env loader
│   │
│   ├── src/                       # PSR-4 Autoloaded Core (Namespace: App\)
│   │   ├── Database/
│   │   │   └── Connection.php     # Singleton PDO database factory
│   │   │
│   │   ├── Models/
│   │   │   └── User.php           # Strongly-typed User DTO (JsonSerializable)
│   │   │
│   │   ├── Repositories/
│   │   │   ├── UserRepositoryInterface.php # Interface contract (DIP)
│   │   │   └── UserRepository.php          # PostgreSQL queries & batch transactions
│   │   │
│   │   ├── Services/
│   │   │   ├── CsvParser.php      # Memory-safe CSV stream reader
│   │   │   ├── UserValidator.php  # Name normalization & RFC email validation
│   │   │   └── UserImporter.php   # Central import engine (shared CLI & Web)
│   │   │
│   │   └── CLI/
│   │       └── CliCommand.php     # CLI options parser & ANSI terminal formatting
│   │
│   ├── public/
│   │   └── index.php              # REST API gateway for React Web UI
│   │
│   ├── tests/                     # Automated PHPUnit Test Suite
│   │   ├── Unit/                  # Isolated parser & validator unit tests
│   │   └── Integration/           # Database & importer integration tests
│   │
│   ├── user_upload.php            # Root CLI entry point
│   ├── composer.json              # Autoloading configuration & dependencies
│   ├── composer.lock              # Deterministic package versions
│   └── .env.example               # Template environment configuration
│
├── frontend/                      # React Web Dashboard (Vite + Tailwind CSS)
│   ├── src/
│   │   ├── components/            # FileUpload, StatsSummary, PreviewTable
│   │   ├── services/              # API HTTP client
│   │   ├── App.jsx                # Main workflow view
│   │   └── main.jsx               # Client entry point
│   ├── package.json
│   └── vite.config.js
│
├── data/
│   └── users.csv                  # Challenge dataset
│
├── .gitignore                     # Excludes .env, vendor/, node_modules/
└── README.md                      # Comprehensive documentation
```

---

## 🗄️ Database Schema Specification

The application persists user records to a PostgreSQL table named `users`:

```sql
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL
);
```

### Persistence Guarantees:
1. **Uniqueness**: The `email` column enforces a strict `UNIQUE` constraint preventing duplicate user entries.
2. **Conflict Handling**: Bulk imports use `ON CONFLICT (email) DO NOTHING` to gracefully skip duplicate emails during batch inserts.
3. **Transaction Safety**: Batches execute inside atomic database transactions (`BEGIN`/`COMMIT`), automatically rolling back changes on connection interruptions.

---

## ⌨️ CLI Directives Specification (`user_upload.php`)

The standalone command-line tool supports the following directives:

| Directive | Description |
|---|---|
| `--file [csv_file]` | Path to the CSV file to be parsed and imported. |
| `--create_table` | Builds the PostgreSQL `users` table and exits cleanly. |
| `--dry_run` | Executes parsing and validation without writing records to the database. |
| `-u [username]` | PostgreSQL database username. |
| `-p [password]` | PostgreSQL database password. |
| `-h [host]` | PostgreSQL database host. |
| `-d [database]` | PostgreSQL database name / port. |
| `--help` | Displays command directives and usage examples. |

---

## 🔄 7-Step Git Development Workflow

Every milestone adheres to a strict engineering lifecycle:
1. **Sync Local Main**: `git checkout main && git pull origin main`
2. **Feature Branch**: `git checkout -b <type>/<feature-name>`
3. **Conventional Commits**: Atomic commits (`feat:`, `chore:`, `test:`, `docs:`)
4. **Push Upstream**: `git push -u origin <type>/<feature-name>`
5. **Pull Request**: Document changes, architectural decisions, and test steps
6. **PR Merge**: Clean merge on GitHub and delete remote branch
7. **Local Cleanup**: `git checkout main && git pull origin main && git branch -d <branch-name>`