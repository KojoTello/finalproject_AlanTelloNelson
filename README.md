# Ashesi Faculty Review Platform

A modern, student-led platform for Ashesi University students to anonymously rate and review faculty members and interns. This project serves as the final submission for the Web Technologies course.

**🔗 Live Website:** https://finalprojectalantellonelson-production.up.railway.app/

## 🚀 Features
- **User Authentication:** Secure signup/login restricted to `@ashesi.edu.gh` domains.
- **Faculty Dashboard:** Searchable and filterable list of professors and interns.
- **Detailed Analytics:** View "Would Take Again" percentages, difficulty ratings, and specific quality metrics.
- **Admin Panel:** Full CRUD capabilities for managing faculty members and moderating reviews.
- **Responsive Design:** Fully optimized for mobile and desktop viewing.

## 🛠️ Tech Stack
- **Frontend:** HTML5, CSS3 (Custom responsive grid), JavaScript (Regex Validation).
- **Backend:** PHP 8.2 (Object-Oriented style).
- **Database:** MySQL (Normalized Schema).
- **Hosting:** Railway (Cloud Deployment).

## ⚙️ Setup Instructions
1. Clone the repository.
2. Import `database.sql` into your MySQL server.
3. Configure `db.php` with your database credentials.
4. Run via Apache/XAMPP or deploy to Railway using the included `composer.json`.


## 🤖 AI Usage Declaration

**1. Tool Names & Providers:**
* **Tool:** Google Gemini, ChatGPT
* **Provider:** Google, OpenAI

**2. Dates of Use:**
* November 2025 – December 2025

**3. Precise Prompts & Strategy:**
* **Conceptual Design:** *"I'm creating a ratemyprofessor website for my school. What rating factors does the original rate my professor have that I can implement?"*
* **Deployment & Configuration:** *"I am deploying my PHP application to Railway. Write a db.php database connection script that automatically detects if it is running on the live Railway server using environment variables (like MYSQLHOST), but falls back to my local XAMPP settings (localhost, root) if those variables are missing. Ensure it handles the port number correctly and hides specific errors on the live site for security."*
* **Coding Assistance:**  *"Create a CSS card design that matches this dashboard screenshot. I used this to figure out how to get my website to look similar to ashesi.edu.gh,  "* and *"Debug this Railway database connection error."*

**4. Outputs Used:**
* **Database Schema Design:** Adopted specific rating factors (Clarity, Helpfulness, Difficulty, Would Take Again) into the `reviews` table structure.
* **Frontend Design:** Utilized AI-generated CSS for the dashboard grid, profile cards, circular avatar zoom effects, and star rating displays.
* **Backend Logic:** Adopted security best practices suggested by the AI, including password hashing (bcrypt), prepared statements for SQL injection prevention, and session-based access control to secure the Admin Portal.
* **Deployment Logic:** Adopted the AI-suggested "dual-mode" `db.php` script to handle both Railway environment variables (`MYSQL_URL`/`MYSQLHOST`) and local XAMPP credentials seamlessly.

**5. Extent of Use:**
* The AI was used to generate initial baseline code for complex logic (like SQL aggregation and environment detection) and design concepts. This code was then heavily modified and customized to fit the specific Ashesi University context (e.g., custom color schemes, specific department names).

**6. Post-Processing & Verification:**
* **Manual Verification:** SQL queries were tested in phpMyAdmin to ensure accurate data retrieval.
* **Design Adjustments:** CSS colors were manually updated to match the Ashesi Maroon (`#701326`) and Gold branding.
* **Security Testing:** Manually verified that AI-suggested PHP code included `htmlspecialchars()` and prepared statements to prevent XSS and SQL injection vulnerabilities.
