# 📝 FINAL PROJECT: MYNOTES - WEB PROGRAMMING & APPLICATIONS

## 1. TEAM MEMBERS
* **Dev A (Frontend UI/UX & Client Logic):** Tran Thi Ngoc Nhu - 524H0021
  * **Roles:** UI/UX Design, Responsive Layouts, Grid/List View toggle, Live Search, Offline PWA capabilities, Real-time WebSocket UI.
* **Dev B (Backend, Database & API):** Nguyen Thanh An - 519H0133
  * **Roles:** Database Design, Authentication (Login/Register/OTP), CRUD operations for Notes/Labels, RESTful APIs, WebSocket Server configuration.

> **Note:** Detailed task delegation is further demonstrated in the source code commit history and the Demo Video.

## 2. VIDEO DEMO LINK
* **YouTube Link:** [Insert your YouTube video link here]

## 3. SYSTEM REQUIREMENTS
To run this project correctly, please ensure your machine has:
* PHP >= 8.1
* Composer
* Node.js & NPM
* MySQL / MariaDB (XAMPP/MAMP/Laragon)

## 4. INSTALLATION & SETUP GUIDE
Please follow these steps strictly to run the application locally:

**Step 1:** Unzip the project folder and open the terminal inside the root directory.

**Step 2:** Install PHP dependencies:
```bash
composer install
```

**Step 3:** Install Node.js dependencies:
```bash
npm install
```

**Step 4:** Copy the environment file:
```bash
cp .env.example .env
```

**Step 5:** Generate the application key:
```bash
php artisan key:generate
```

**Step 6:** Configure your Database connection in the `.env` file. Open XAMPP/MySQL and create a database named `mynotes_db`, then update the `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mynotes_db
DB_USERNAME=root
DB_PASSWORD=
```

**Step 7:** Run database migrations and seeders (to load tables and test data):
```bash
php artisan migrate --seed
```

**Step 8:** Build Frontend assets (Vite/Mix):
```bash
npm run build
```

**Step 9:** Start the Laravel local server:
```bash
php artisan serve
```
👉 The application will be accessible at: `http://localhost:8000`

## 5. TEST ACCOUNTS (CREDENTIALS)
For evaluation purposes, we have seeded the following test accounts with pre-loaded data:

**[Account 1 - Note Owner]**
* **Email:** user1@tdtu.edu.vn
* **Password:** password123

**[Account 2 - Collaborator]** *(Used for testing Shared Notes & Real-time features)*
* **Email:** user2@tdtu.edu.vn
* **Password:** password123

## 6. SPECIAL CONFIGURATIONS

### Real-time WebSocket
* We utilized Laravel Reverb (or Pusher/Echo) for Real-time UI collaboration.
* Please ensure you have internet access, or run the local WebSocket server using: 
```bash
php artisan reverb:start
```

### Progressive Web App & Offline Mode
* To test the Offline Mode, please use Google Chrome or Microsoft Edge.
* Open **Developer Tools (F12)** -> Navigate to the **Network** tab -> Change throttling to **Offline**.
* The application will utilize the Service Worker (`sw.js`) and IndexedDB to cache the UI and save notes locally without an internet connection.

---
*Thank you for reviewing our project!*