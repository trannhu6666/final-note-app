# 📝 FINAL PROJECT: MYNOTES - WEB PROGRAMMING & APPLICATIONS

## 1. TEAM MEMBERS

### Dev A (Frontend UI/UX, Client Logic & Full-Stack Integration)
- **Name:** Tran Thi Ngoc Nhu  
- **Student ID:** 524H0021  

#### Responsibilities
- UI/UX & Frontend Core
- Client-Side Logic
- Routing & API Architecture
- Backend Controller Enhancement
- Advanced Feature Implementation:
- Localization & Compliance
- DevOps & Environment Debugging

---

### Dev B (Backend, Database & API)
- **Name:** Nguyen Thanh An  
- **Student ID:** 519H0133  

#### Responsibilities
- Database Design
- Authentication (Login/Register/OTP)
- CRUD Operations for Notes/Labels
- RESTful APIs
- WebSocket Server Configuration

> **Note:** Detailed task delegation is further demonstrated in the source code commit history and the Demo Video.

---

# 2. VIDEO DEMO LINK

- **YouTube Link:** [Insert your YouTube video link here]

---

# 3. SYSTEM REQUIREMENTS

To run this project seamlessly, ensure you have installed on your host machine:

- Docker
- Docker Compose

> No local installations of PHP, Node.js, or XAMPP/MySQL are required because the entire system runs inside isolated Docker containers.

---

# 4. INSTALLATION & SETUP GUIDE (DOCKER DEPLOYMENT)

Follow these steps carefully to build and start the entire application infrastructure locally. This guide assumes you have just unzipped the project.

---

## Step 1: Prepare the Environment

Unzip the project folder and open a terminal inside the root directory. Copy the environment configuration file:

```bash
cp .env.example .env
```

---

## Step 2: Start the Docker Containers

Build and run all backend, web server, and database services using Docker Compose:

```bash
docker-compose up -d --build
```

---

## Step 3: Install Backend Dependencies

Use the included Composer executable to install Laravel's core packages inside the PHP container:

```bash
docker-compose exec app php composer.phar install
```

> ⚠️ **IMPORTANT WARNING:**  
> Do **NOT** open or edit the `composer.phar` file in your code editor (like VS Code). It is a compiled binary archive, not a regular text file. Opening it will cause your editor to display syntax errors, and saving it may permanently corrupt the file!

---

## Step 4: Configure the Application

Generate the Laravel application encryption key to secure your session data:

```bash
docker-compose exec app php artisan key:generate
```

---

## Step 5: Initialize Database & Permissions

> **💡 Note for Windows Users:** Do not worry about seeing Linux commands like `touch` or `chmod`. These commands are executed directly inside the Linux-based Docker container, so they will work perfectly on your Windows Command Prompt, PowerShell, or Git Bash!

Run the following commands sequentially to create the isolated SQLite file, set proper folder permissions, and seed the test data:

```bash
# 1. Initialize an empty SQLite database file
docker-compose exec app touch database/database.sqlite

# 2. Create a symbolic link for uploaded files (e.g., User Avatars)
docker-compose exec app php artisan storage:link

# 3. Grant full read/write permissions to prevent 500 Internal Server Errors
docker-compose exec app chmod -R 777 database storage bootstrap/cache public

# 4. Run fresh migrations and seed pre-defined test accounts
docker-compose exec app php artisan migrate:fresh --seed

## Step 6: Build Frontend Assets (Vite)

Use an isolated Node.js container to install NPM packages and compile the frontend interface without crashing the active WebSocket server:

```bash
# 1. Install Node modules
docker-compose run --rm websocket npm install

# 2. Build the production frontend assets (CSS/JS)
docker-compose run --rm websocket npm run build
```

---

## Step 7: Final Permissions & Restart

Because the Node container generates frontend build files with root privileges, you must explicitly grant read/write permissions to the `public` and `storage` directories. This ensures Nginx can serve your CSS/JS files and users can successfully upload Avatar images.

```bash
# 1. Grant permissions for assets and image uploads
docker-compose exec app chmod -R 777 public storage

# 2. Restart services to apply all configurations to the WebSocket server
docker-compose up -d
```

---

# Application Access

After completing all 7 steps successfully, access the application at:

```text
http://localhost
```

# 5. TEST ACCOUNTS (CREDENTIALS)

The following pre-seeded accounts are available for evaluation and testing purposes.

## Account 1 — Note Owner

```txt
Email: user1@tdtu.edu.vn
Password: password123
```

---

## Account 2 — Collaborator
Used for testing Shared Notes and Real-time collaboration features.

```txt
Email: user2@tdtu.edu.vn
Password: password123
```

---

# 6. SPECIAL CONFIGURATIONS & ARCHITECTURE

## Real-time Node.js WebSocket Server

Real-time collaboration and concurrent editing features are handled through a dedicated Node.js WebSocket server:

```txt
websocket-server.js
```

The server runs on:

```txt
Port 8080
```

The WebSocket service is automatically started when running:

```bash
docker-compose up -d
```

No additional manual commands are required.

---

## Progressive Web App (PWA) & Offline Capabilities

To test offline functionality:

1. Open the application using:
   - Google Chrome
   - Brave
   - Microsoft Edge

2. Open Developer Tools:
   - Press `F12`

3. Navigate to:
   - `Network` tab

4. Change network throttling mode:
   - From `No throttling`
   - To `Offline`

The application will continue functioning using:
- Registered Service Worker (`sw.js`)
- Client-side IndexedDB Storage

This allows note interactions and modifications to persist locally even without an internet connection.
