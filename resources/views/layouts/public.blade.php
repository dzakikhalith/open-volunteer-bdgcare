<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? ($siteSettings['site_name'].' - '.$siteSettings['tagline']) }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark-teal" href="{{ route('home') }}">
                {{ $siteSettings['site_name'] }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tentang">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('events.index') }}">Daftar Event</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('registrations.track') }}">Cek Pendaftaran</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kontak">Kontak</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-cherry" href="{{ route('events.index') }}">Lihat Event</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @include('components.alert')
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <footer class="bg-dark-teal text-white mt-5">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">{{ $siteSettings['site_name'] }}</h5>
                    <p class="mb-0">{{ $siteSettings['footer_text'] }}</p>
                </div>
                <div class="col-lg-4">
                    <h6 class="fw-semibold mb-3">Tautan</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a class="text-white text-decoration-none" href="{{ route('home') }}">Beranda</a></li>
                        <li><a class="text-white text-decoration-none" href="{{ route('events.index') }}">Daftar Event</a></li>
                        <li><a class="text-white text-decoration-none" href="{{ route('registrations.track') }}">Cek Pendaftaran</a></li>
                        <li><a class="text-white text-decoration-none" href="#tentang">Tentang Kami</a></li>
                        <li><a class="text-white text-decoration-none" href="{{ route('admin.login') }}">Login Admin</a></li>
                    </ul>
                </div>
                <div class="col-lg-4" id="kontak">
                    <h6 class="fw-semibold mb-3">Kontak</h6>
                    @if ($siteSettings['contact_address'])
                        <p class="mb-1"><i class="bi bi-geo-alt me-2"></i>{{ $siteSettings['contact_address'] }}</p>
                    @endif
                    @if ($siteSettings['contact_email'])
                        <p class="mb-1"><i class="bi bi-envelope me-2"></i>{{ $siteSettings['contact_email'] }}</p>
                    @endif
                    @if ($siteSettings['contact_phone'])
                        <p class="mb-1"><i class="bi bi-telephone me-2"></i>{{ $siteSettings['contact_phone'] }}</p>
                    @endif
                    @if ($siteSettings['instagram'])
                        <p class="mb-0"><i class="bi bi-instagram me-2"></i>{{ $siteSettings['instagram'] }}</p>
                    @endif
                </div>
            </div>
            <hr class="border-light opacity-25 my-4">
            <div class="text-center small opacity-75">
                &copy; {{ date('Y') }} {{ $siteSettings['site_name'] }}. All rights reserved.
            </div>
        </div>
    </footer>
</body>

</html>
