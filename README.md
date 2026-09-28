# CRM System

A comprehensive, custom PHP-based Customer Relationship Management (CRM) system designed to manage customers, assign leads to employees, track call histories, and schedule follow-ups.

## 🚀 Features Overview

### Global Features
- **Role-Based Access Control:** Distinct dashboards and permissions for **Super Admins** and **Employees**.
- **Call Logging & Recording:** Log call notes, attach call recording files (e.g., MP3s), and maintain a thorough communication history.
- **Status & Interest Tracking:** Categorize leads by interest levels (`Interested`, `Not Interested`, `Follow Up`).
- **Follow-ups:** Schedule specific dates to follow up with leads and track upcoming tasks.
- **Date-wise & List-wise Views:** Organize and filter customer lists using standard tables or specific date groupings.
- **History Tracking:** Comprehensive tracking of call histories and lead assignment histories.

### 👑 Super Admin Features
- **Dashboard:** High-level overview of system metrics and activities.
- **Employee Management:** Create, view, and edit employee accounts.
- **Customer Management:** Full CRUD (Create, Read, Update, Delete) access to the entire customer database.
- **Bulk Operations:** Import customers in bulk using the Bulk Import tool.
- **Lead Assignment:** Assign specific customers or leads to designated employees.
- **Global Call Records:** View all calls made by any employee across the system.
- **Admin Calling:** Capability to call customers directly from the admin interface.

### 👔 Employee Features
- **Personalized Dashboard:** View assigned tasks, calls, and metrics.
- **My Customers:** Access only the leads specifically assigned to them by the admin.
- **Calling Interface:** Dedicated "Start Calling" features to interact with leads efficiently.
- **Personal History:** View detailed logs and recordings of all personal calls made.
- **Follow-up Management:** Track and manage daily callbacks for their assigned leads.

---

## 🛠️ Technology Stack
- **Backend:** PHP (Custom Structure, no heavy frameworks)
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript (Custom styles in `/assets`)
- **Package Management:** Composer

---

## ⚙️ Installation & Setup (Localhost)

Follow these steps to run the project locally using a server like **XAMPP**:

1. **Clone the repository:**
   Move the project folder into your XAMPP `htdocs` directory.
   ```bash
   git clone https://github.com/Kuldeeph10/crm-system.git
   ```

2. **Start your local server:**
   Open the XAMPP Control Panel and start **Apache** and **MySQL**.

3. **Database Setup:**
   - Go to `http://localhost/phpmyadmin/`
   - Create a new database named `crm_system`.
   - Import the `crm_system.sql` file provided in the root directory.

4. **Run the Application:**
   Open your browser and navigate to the localhost path:
   ```
   http://localhost/crm_system/
   ```
   *Note: Ensure the folder name in `htdocs` matches the URL. The system's `.htaccess` file will automatically route the root URL to the login page.*

## 📂 Directory Structure Highlights
- `/api/` - Backend API endpoints and handlers.
- `/pages/` - Frontend views (separated by `admin` and `employee`).
- `/components/` - Reusable UI components (sidebars, navbars, alerts).
- `/assets/` - CSS stylesheets, JavaScript files, and images.
