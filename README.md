# GSGSG SMART 🎓
> **Scholarship Management, Academic Review & Tracking System**  
> Developed for Ghazali Shafie Graduate School of Government (GSGSG), Universiti Utara Malaysia (UUM).

---

## 📌 About the Project

**GSGSG SMART** is an academic administration platform designed to track, audit, and manage scholarship recipients across postgraduate and undergraduate degree programmes. It automates CGPA and GPA calculations across semesters, flags student standing according to institutional scholarship retention thresholds, and produces formal, print-ready compliance audit reports for academic committee review boards.

---

## ✨ Key Features

- **Executive Analytics Dashboard**: Real-time KPI summaries for total enrolled scholars, active recipients, at-risk candidates, and revocations with cohort-wide distribution charts.
- **Automated Retention Standing & Alert Engine**:
  - 🟢 **Active (Good Standing)**: $\text{CGPA} \ge 3.75$
  - 🟡 **At Risk (Watchlist)**: $3.50 \le \text{CGPA} < 3.75$
  - 🔴 **Revoked / Review Required**: $\text{CGPA} < 3.50$
  - Optional administrative override with notes audit trail.
- **Individual Student Profiles & Academic Records**: Semester-by-semester course and grade management with automatic credit-weighted GPA/CGPA computation.
- **Formal Committee & Board Reporting**:
  - Official institutional letterhead and reference numbering.
  - Formatted for A4 / PDF export with print stylesheets (`no-print` controls, signature boxes, official board seal).
  - Customizable signatories directly via the interface.
- **Bulk CSV Data Operations**: Drag-and-drop CSV importer with schema verification and one-click cohort CSV exports.
- **Lightweight Architecture**: Vanilla PHP, modern CSS design tokens with responsive layout, and async REST API (no heavy frameworks or node dependencies).

---

## 🛠️ Technology Stack

- **Backend**: PHP 8.0+ (PDO MySQL / Native JSON API)
- **Database**: MySQL / MariaDB (InnoDB with foreign key constraints & cascade rules)
- **Frontend**: Vanilla JavaScript (ES6+ async/await API layer), CSS3 Custom Properties (Design System)
- **Web Server**: Apache / XAMPP

---

## 📁 Project Directory Structure

```text
GSGSG-SMART/
├── api/                     # Backend REST API
│   ├── controllers/         # Controller logic (Student, Course, Report, Dashboard)
│   ├── db.php               # Database connection & schema migration helper
│   ├── seed.php             # Sample demo dataset seeder
│   └── students.php         # Student API endpoint handler
├── includes/                # Shared layout templates
│   ├── header.php           # Global HTML header & metadata
│   ├── sidebar.php          # Navigation sidebar & brand assets
│   └── footer.php           # Modals, dialogs & script loader
├── js/                      # Frontend JavaScript
│   ├── api.js               # Centralized asynchronous API client
│   ├── common.js            # Shared UI utilities, toasts & dialog controllers
│   ├── dashboard.js         # Dashboard analytics renderer
│   ├── reports.js           # Printable compliance report engine
│   ├── student-detail.js    # Student profile & transcript controller
│   └── students.js          # Roster table, search, filters & pagination
├── pages/                   # User-facing web pages
│   ├── dashboard.php        # Executive dashboard view
│   ├── reports.php          # Official committee audit report view
│   ├── student-detail.php   # Individual student profile view
│   └── students.php         # Student roster view
├── styles.css               # Unified stylesheet & design system
├── .env.example             # Environment configuration template
├── .htaccess                # Apache routing & security rules
├── index.php                # Entry point redirect
└── README.md                # Documentation
```

---

## 🚀 Getting Started (Local Setup)

### 1. Prerequisites
- [XAMPP](https://www.apachefriends.org/) (with Apache & MySQL/MariaDB running PHP 8.0+)
- Git

### 2. Installation
Clone the repository into your local web server root (e.g. `C:/xampp/htdocs/`):

```bash
cd C:/xampp/htdocs
git clone https://github.com/LynxPlayz24/GSGSG-SMART.git
cd GSGSG-SMART
```

### 3. Environment Configuration
Copy `.env.example` to `.env` and adjust credentials if needed:

```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=smart_db
DB_USER=root
DB_PASS=
```

*(Note: `api/db.php` will automatically create the database and tables upon first request if they do not exist).*

### 4. Optional: Configure Virtual Host
To access via `http://gsgsg-smart.uum.edu.my`:

1. **Add to `C:\xampp\apache\conf\extra\httpd-vhosts.conf`**:
   ```apache
   <VirtualHost *:80>
       DocumentRoot "C:/xampp/htdocs/GSGSG-SMART"
       ServerName gsgsg-smart.uum.edu.my
       <Directory "C:/xampp/htdocs/GSGSG-SMART">
           Options FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
2. **Add to `C:\Windows\System32\drivers\etc\hosts`**:
   ```text
   127.0.0.1 gsgsg-smart.uum.edu.my
   ```
3. **Restart Apache** in XAMPP Control Panel.

---

## 📄 License
Internal academic management tool for Universiti Utara Malaysia (UUM). All rights reserved.
