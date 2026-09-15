<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') - {{ config('app.name', 'Hardware Inventory') }}</title>

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Custom CSS (your color scheme) -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="login-page">

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card login-card shadow-lg" style="max-width: 420px; width: 100%;">

            <div class="card-header bg-primary text-white text-center py-4">
                <h2 class="mb-0">{{ config('app.name', 'BuildRight Hardware') }}</h2>
                <p class="mb-0 small">@yield('subtitle', 'Inventory Management System')</p>
            </div>

            <div class="card-body p-4">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>