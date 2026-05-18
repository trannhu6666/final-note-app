<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Note Management - Final Project</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
    <style>
        /* --- CUSTOM PINK THEME --- */
        :root {
            --bs-primary: #e83e8c;
            --bs-primary-rgb: 232, 62, 140;
            --note-font-size: 1rem;
            --note-bg-color: #ffffff;
        }

        .bg-primary {
            background-color: var(--bs-primary) !important;
        }

        .text-primary {
            color: var(--bs-primary) !important;
        }

        .btn-primary {
            background-color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
            color: #fff !important;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: #d63384 !important;
            border-color: #d63384 !important;
        }

        .btn-outline-primary {
            color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus,
        .btn-outline-primary:active {
            background-color: var(--bs-primary) !important;
            color: #fff !important;
        }

        /* --- DARK MODE OVERRIDES --- */
        [data-bs-theme="dark"] {
            --note-bg-color: #2b3035;
        }

        [data-bs-theme="dark"] .note-card {
            color: #f8f9fa !important;
        }

        [data-bs-theme="dark"] body {
            background-color: #121212 !important;
        }

        [data-bs-theme="dark"] .navbar {
            background-color: #1a1d20 !important;
            border-bottom: 1px solid #2b3035;
        }

        [data-bs-theme="dark"] .form-control {
            background-color: #2b3035;
            border-color: #495057;
            color: #fff;
        }

        [data-bs-theme="dark"] .form-control::placeholder {
            color: #adb5bd;
        }

        [data-bs-theme="dark"] .input-group-text {
            background-color: #2b3035;
            border-color: #495057;
            color: #fff;
        }

        /* --- USER PREFERENCES FOR ALL NOTE CARDS --- */
        .note-card {
            background-color: var(--note-bg-color, #ffffff) !important;
        }

        .note-card .card-title {
            font-size: calc(var(--note-font-size, 1rem) + 0.25rem) !important;
        }

        .note-card .card-text {
            font-size: var(--note-font-size, 1rem) !important;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="/notes"><i class="bi bi-journal-text"></i> MyNotes</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <form class="d-flex mx-auto w-50 my-2 my-lg-0" onsubmit="event.preventDefault();">
                    <div class="input-group">
                        <span class="input-group-text border-0"><i class="bi bi-search"></i></span>
                        <input class="form-control border-0 shadow-none" type="search" id="search-box"
                            placeholder="Quick search notes..." aria-label="Search">
                    </div>
                </form>

                <span id="offline-badge" class="badge bg-danger d-none me-3">
                    <i class="bi bi-wifi-off"></i> Offline Mode
                </span>

                <ul class="navbar-nav align-items-center">
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="userPrefDropdown" role="button"
                            data-bs-toggle="dropdown"><i class="bi bi-gear-fill"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <h6 class="dropdown-header">Theme</h6>
                            </li>
                            <li><a class="dropdown-item" href="#" id="theme-light"><i class="bi bi-sun me-2"></i>
                                    Light</a></li>
                            <li><a class="dropdown-item" href="#" id="theme-dark"><i class="bi bi-moon-stars me-2"></i>
                                    Dark</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <h6 class="dropdown-header">Note Font Size</h6>
                            </li>
                            <li><a class="dropdown-item font-size-btn" href="#" data-size="0.875rem">Small</a></li>
                            <li><a class="dropdown-item font-size-btn" href="#" data-size="1rem">Medium</a></li>
                            <li><a class="dropdown-item font-size-btn" href="#" data-size="1.25rem">Large</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <h6 class="dropdown-header">Default Note Color</h6>
                            </li>
                            <li class="px-3 py-1">
                                <input type="color" class="form-control form-control-color w-100" id="defaultNoteColor"
                                    value="#ffffff" title="Choose your color">
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="accountDropdown" role="button"
                            data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="/profile/edit"><i class="bi bi-person me-2"></i>
                                    Profile</a></li>
                            <li><a class="dropdown-item" href="/notes/shared"><i class="bi bi-people me-2"></i> Shared
                                    with me</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="/logout"><i
                                        class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="unverified-banner" class="alert alert-warning text-center d-none mb-0 rounded-0 shadow-sm"
        style="z-index: 1000;" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>
        <strong>Account Unverified!</strong> You can currently use all features, but please check your email
        and click the activation link to complete the process.
    </div>

    <main class="container-fluid px-4 mt-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // 1. Theme Logic
        const savedTheme = localStorage.getItem('user_theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);

        document.getElementById('theme-dark').addEventListener('click', (e) => {
            e.preventDefault();
            document.documentElement.setAttribute('data-bs-theme', 'dark');
            localStorage.setItem('user_theme', 'dark');
        });

        document.getElementById('theme-light').addEventListener('click', (e) => {
            e.preventDefault();
            document.documentElement.setAttribute('data-bs-theme', 'light');
            localStorage.setItem('user_theme', 'light');
        });

        // 2. Font Size Logic
        const fontBtns = document.querySelectorAll('.font-size-btn');
        fontBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const newSize = e.target.getAttribute('data-size');
                document.documentElement.style.setProperty('--note-font-size', newSize);
            });
        });

        // 3. Color Logic
        const colorPicker = document.getElementById('defaultNoteColor');
        if (colorPicker) {
            colorPicker.addEventListener('input', (e) => {
                const newColor = e.target.value;
                document.documentElement.style.setProperty('--note-bg-color', newColor);
            });
        }

        // 4. Offline UI Logic
        window.addEventListener('online', updateNetworkStatus);
        window.addEventListener('offline', updateNetworkStatus);
        function updateNetworkStatus() {
            const badge = document.getElementById('offline-badge');
            if (badge) {
                navigator.onLine ? badge.classList.add('d-none') : badge.classList.remove('d-none');
            }
        }

        // 🌟 5. UNVERIFIED ACCOUNT CHECK LOGIC (Global)
        document.addEventListener('DOMContentLoaded', function () {
            const token = localStorage.getItem('user_token');
            if (token) {
                fetch('/api/user/profile', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    }
                })
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success' && response.data) {
                            const user = response.data;
                            const banner = document.getElementById('unverified-banner');

                            // Check if is_active = 0 or email_verified_at = null
                            if (user.is_active == 0 || user.is_active === false) {
                                banner.classList.remove('d-none'); // Show the yellow banner
                            } else {
                                banner.classList.add('d-none'); // Hide the banner if activated
                            }
                        }
                    })
                    .catch(err => console.error("Error checking activation status:", err));
            }
        });

    </script>
</body>

</html>