# 📝 FINAL PROJECT: MYNOTES - WEB PROGRAMMING & APPLICATIONS

## 1. TEAM MEMBERS

### Dev A (Frontend UI/UX & Client Logic)
- **Name:** Tran Thi Ngoc Nhu  
- **Student ID:** 524H0021  

#### Responsibilities
- UI/UX Design
- Responsive Layouts
- Grid/List View Toggle
- Live Search
- Offline PWA Capabilities
- Real-time WebSocket UI

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

Follow these steps carefully to build and start the entire application infrastructure locally.

## Step 1
Unzip the project folder and open a terminal inside the root directory.

---

## Step 2
Copy the environment configuration file:

```bash
cp .env.example .env
```

---

## Step 3
Build and run all backend, frontend, and database services using Docker Compose:

```bash
docker-compose up -d --build
```

This command automatically orchestrates and launches:
- Nginx Web Server
- PHP-FPM Application Container
- Automated Node.js Environment

---

## Step 4
Generate the Laravel application encryption key inside the running container:

```bash
docker-compose exec app php artisan key:generate
```

---

## Step 5
Run database migrations and seeders:

```bash
docker-compose exec app php artisan migrate --seed
```

> The project uses an isolated SQLite database located at:

```txt
/var/www/database/database.sqlite
```

This setup improves container portability and simplifies deployment.

---

## Step 6
Build production frontend assets using Vite:

```bash
docker-compose exec app npm run build
```

---

## Application Access

After all services are started successfully, access the application at:

```txt
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
