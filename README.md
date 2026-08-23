# User Data Importer (PHP 8.3 + PostgreSQL + React)

A production-grade, high-performance CSV user import application featuring a shared PHP 8.3 OOP engine, transactional PostgreSQL persistence via PDO, a standalone CLI utility, and an interactive React web dashboard.

## 🚀 Key Features

- **Shared Core Business Engine**: DRY design shared identically across CLI and Web REST API.
- **Robust CSV Parsing & Validation**: Memory-safe streaming, title-casing of names, email lowercasing/trimming, strict RFC validation, and duplicate detection.
- **PostgreSQL Persistence via PDO**: Prepared statements, transaction support, and unique constraint enforcement on email.
- **Standalone CLI Tool (`user_upload.php`)**: Full support for `--file`, `--create_table`, `--dry_run`, `-u`, `-p`, `-h`, `-d`, and `--help`.
- **Interactive React Preview Dashboard**: Modern UI with drag-and-drop CSV upload, validation metrics summary, and live status badges.
- **Automated PHPUnit Test Suite**: Full unit and integration coverage.

## 📁 Repository Structure

```text
user-data-importer/
├── backend/
│   ├── config/            # Database and environment configurations
│   ├── src/               # PSR-4 Autoloaded Core (App\)
│   │   ├── Database/      # PDO Connection & Factory
│   │   ├── Models/        # DTOs / Entities
│   │   ├── Repositories/  # Persistence Layer
│   │   ├── Services/      # CSV Parser, Validator, Importer
│   │   └── CLI/           # CLI Command Parser & Output Formatter
│   ├── public/            # REST API Gateway (index.php)
│   ├── tests/             # PHPUnit Test Suite
│   └── composer.json      # Dependencies and Autoloading
├── frontend/              # React Web Dashboard (Vite + Tailwind CSS)
├── data/
│   └── users.csv          # Challenge dataset
├── .gitignore
└── README.md
```

## 🛠️ Requirements

- **PHP**: 8.3+ (with `pdo_pgsql` extension enabled)
- **Composer**: 2.x
- **PostgreSQL**: 16+
- **Node.js**: 18+ (for frontend)