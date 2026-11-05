<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login Admin - Bandung Care</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="text-center mb-4">
                <h1 class="h3 fw-bold text-dark-teal">Bandung Care</h1>
                <p class="text-muted">Login Dashboard Admin</p>
            </div>

            @include('components.alert')

            <div class="card card-rounded shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>
                        <button type="submit" class="btn btn-cherry w-100">Masuk</button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('home') }}" class="text-decoration-none text-dark-teal">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>

</html>
