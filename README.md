# User Data Importer

A robust, high-performance user data import and processing engine with automated validation and reporting built with PHP 8.3, PostgreSQL, and React.

## Features
- **Shared Business Logic**: Core parsing and validation service shared across CLI and Web interfaces.
- **CLI Tool**: Standalone `user_upload.php` utility supporting batch imports, dry-runs, and automatic table creation.
- **Web Dashboard**: Interactive React UI with real-time CSV preview, validation badges, and import execution.
- **Data Integrity**: PostgreSQL persistence with unique constraints and transaction-safe batch processing.
- **Automated Testing**: Unit and integration test suite using PHPUnit.