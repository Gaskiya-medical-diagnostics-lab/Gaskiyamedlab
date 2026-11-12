# Gaskiya Medical Diagnostics and Research Laboratory — LIMS

A complete, lightweight PHP & MySQL **Laboratory Information Management System (LIMS)** built for **Gaskiya Medical Diagnostics and Research Laboratory**, ready for **GitHub** version control and **InfinityFree** shared PHP/MySQL hosting.

## Features
- **Public Diagnostic Portal (`index.php`)**: Showcases laboratory departments, live searchable test catalog with transparent pricing, and online walk-in/home sample collection booking.
- **Staff LIMS Dashboard (`dashboard.php`)**: Secure role-based staff login (`login.php`), patient registration with automatic `GSK-YYMM-XXXX` Lab Number generation, bench worklist management, and result verification.
- **Patient & Clinician Result Verification (`verify_result.php`)**: Online result lookup by Lab Number with a clean, print-ready official laboratory letterhead report.
- **Zero-Framework Footprint**: Pure PHP 8+, MySQLi prepared statements, Bootstrap 5, and FontAwesome—runs out of the box on InfinityFree without Composer or SSH.

## Deployment Guide (GitHub + InfinityFree)
1. **Push to GitHub**: Upload all files in this folder to a GitHub repository.
2. **Create InfinityFree Hosting & Database**:
   - Log into your InfinityFree Client Area and create a MySQL Database (e.g., `if0_XXXXXX_gaskiya`).
   - Open **phpMyAdmin** for that database and import `database.sql`.
3. **Configure Database Credentials**:
   - Edit `config/db.php` and update `DB_HOST`, `DB_USER`, `DB_PASS`, and `DB_NAME` with your InfinityFree MySQL details.
4. **Upload via FTP / File Manager**:
   - Upload the contents of this folder directly into your domain's `htdocs/` directory on InfinityFree.
5. **Default LIMS Staff Login**:
   - **Username:** `admin`
   - **Password:** `password123` *(Change immediately after first login)*
