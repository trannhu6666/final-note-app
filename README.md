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
- Advanced Feature Implementation
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

- **GoogleDrive Link:** https://drive.google.com/file/d/141PhhwVy1zpAf2kWsjNqBXGLGXlohLw2/view?usp=sharing

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
# For Linux / macOS / Git Bash / Command Prompt
cp .env.example .env

# For Windows PowerShell
copy .env.example .env
```

---

## Step 2: Install Backend Dependencies (Vendor Generation)

Since the production-optimized PHP backend container does not include a global development-ready Composer binary, we fetch all necessary core Laravel packages using an isolated, official Composer image from Docker Hub:

```bash
# For Linux / macOS / Git Bash / Command Prompt
docker run --rm -v $(pwd):/app composer install

# For Windows PowerShell
docker run --rm -v ${PWD}:/app composer install
```

---

## Step 3: Start the Docker Containers

Build and initiate the backend ecosystem, web server, and WebSocket containers in detached background mode:

```bash
docker-compose up -d --build
```

---

## Step 4: Configure the Application

Generate the unique Laravel application encryption key to secure session cookies and data transmission hashes:

```bash
docker-compose exec app php artisan key:generate
```

---

## Step 5: Initialize Database & Permissions

> **💡 Note for Windows Users:** Do not worry about seeing Linux commands like `touch` or `chmod`. These commands are executed directly inside the Linux-based Docker container, so they will work perfectly on your Windows Command Prompt, PowerShell, or Git Bash!

Run the following commands sequentially to create the isolated SQLite file, link attachment assets, and seed the testing data:

```bash
# 1. Initialize an empty SQLite database file
docker-compose exec app touch database/database.sqlite

# 2. Create a symbolic link for uploaded files (e.g., User Avatars, Note Images)
docker-compose exec app php artisan storage:link

# 3. Grant full read/write permissions to directories to prevent 500 internal errors
docker-compose exec app chmod -R 777 database storage bootstrap/cache public

# 4. Run fresh migrations and seed pre-defined evaluation accounts
docker-compose exec app php artisan migrate:fresh --seed
```

---

## Step 6: Build Frontend Assets (Vite)

Use an isolated Node.js container to install NPM packages and compile the frontend production interface bundles without crashing the active network ports:

```bash
# 1. Install Node modules
docker-compose run --rm websocket npm install

# 2. Build the production frontend assets (CSS/JS)
docker-compose run --rm websocket npm run build
```

---

## Step 7: Final Permissions & Container Reset (Prevent 502 Errors)

Because the Node container compiles static assets using root privileges, we must reset the directory permissions. Additionally, to clear Nginx's internal upstream DNS caching and completely prevent 502 Bad Gateway errors, flush and sync the containers simultaneously:

```bash
# 1. Grant absolute permissions for compiled assets and image uploads
docker-compose exec app chmod -R 777 public storage

# 2. Hard-reset the infrastructure to bind Nginx routing with the fresh PHP upstream container
docker-compose down && docker-compose up -d
```

---

# Application Access

After completing all 7 steps successfully, access the application interface at:

```text
http://localhost
```

---

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

## Account Verification / Activation Flow Simulation
To fulfill the **Better Approach** requirement for secure user authentication, newly registered accounts are set to `is_active = false` by default. An activation token is securely generated via Laravel's Cache facade and piped into the backend logging streams.

To retrieve the simulation activation link and verify a profile, read the live telemetry log trace:
```bash
cat storage/logs/laravel.log
```
Simply extract the generated URL block (e.g., `http://localhost/verify?email=...&token=...`) from the text stream and paste it into your active browser tab to dynamically unlock the account.

---

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
- Registered Service Worker (`public/sw.js`)
- Client-side IndexedDB Transaction Tables

This allows note interactions, tag attachments, and structural modifications to persist locally when disconnected, queueing background synchronizations to update the main cloud servers once connectivity returns.
