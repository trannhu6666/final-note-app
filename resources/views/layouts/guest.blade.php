<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyNotes - Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --bs-primary: #e83e8c;
            --bs-primary-rgb: 232, 62, 140;
        }

        body {
            background-color: #f8f9fa;
        }

        .bg-primary {
            background-color: var(--bs-primary) !important;
        }

        .btn-primary {
            background-color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
        }

        .btn-primary:hover {
            background-color: #d63384 !important;
        }

        .btn-outline-secondary:hover {
            background-color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
            color: #fff !important;
        }

        .text-primary {
            color: var(--bs-primary) !important;
        }

        .hero-section {
            min-height: 80vh;
            display: flex;
            align-items: center;
        }

        .auth-container {
            min-height: 75vh;
            display: flex;
            align-items: center;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/"><i class="bi bi-journal-text"></i> MyNotes</a>
        </div>
    </nav>
    <main class="flex-grow-1">
        @yield('content')
    </main>
    <footer class="bg-white text-center py-4 mt-auto border-top">
        <div class="container">
            <p class="text-muted mb-0">&copy; 2026 MyNotes - FIT TDTU</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>