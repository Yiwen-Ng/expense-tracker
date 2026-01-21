Expense Tracker
A full-stack web application designed to track expenses and income across multiple currencies. This project features a robust PHP REST API and a dynamic Vue.js 3 frontend.

Features
- Real-time Tracking : Add, edit, and delete transactions instantly.
- Currency Insights  : Automatically groups and sums expenses by currency (USD, EUR, etc.).
- Modern UI.         : Built with Vue.js, featuring a clean interface and responsive design.
- Secure Backend.    : PHP-driven API using prepared statements to ensure data integrity.

Tech Stack
- Frontend : Vue.js, JavaScript, CSS3
- Backend  : PHP (MySQLi)
- Database : MySQL
- API	   : RESTful JSON API

Project Structure

(A) Backend (PHP API)

The backend handles data persistence and business logic:
- create.php   : Validates and saves new transactions.
- fetch.php    : Retrieves the transaction history.
- summary.php  : Aggregates totals and counts per currency.
- update.php   : Manages existing record modifications.
- delete.php   : Deletes existing transactions.
- database.php : Centralized PDO/MySQLi connection logic.

(B) Frontend (Vue.js)

The App.vue file contains the core logic for:
- Fetching data from the API on mount.
- State management for the transaction list and summary stats.
- Form handling for adding new entries.

Installation & Setup

(A) Database Setup
1. Create a MySQL database (e.g., transaction_db).
2. Run the provided SQL schema:
-- Located in schema.sql
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) NOT NULL,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

(B) Backend Configuration
Update database.php with your local credentials:
PHP
$host = "localhost";
$db_name = "transaction_db";
$username = "your_username";
$password = "your_password";

(C) Frontend Setup
Ensure your Vue app is pointing to the correct local server URL (e.g., http://localhost/api/).

API Endpoints
GET	    /fetch.php   : List all transactions
GET	    /summary.php : Get totals grouped by currency
POST	/create.php  : Add a new transaction
POST	/update.php	 : Update an existing record
POST	/delete.php	 : Delete a record (requires id)