<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard Admin - Bandung Care')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="d-flex min-vh-100">
        <aside class="bg-dark-teal text-white d-none d-lg-flex flex-column" style="width: 280px;">
            <div class="p-4 border-bottom border-light border-opacity-25">
                <a href="{{ route('admin.dashboard') }}" class="text-white text-decoration-none">
                    <h5 class="mb-0 fw-bold">Bandung Care</h5>
                    <small class="opacity-75">Admin Dashboard</small>
                </a>
            </div>
            <nav class="flex-grow-1 p-3 d-flex flex-column gap-1">
                <a class="text-white text-decoration-none px-3 py-2 rounded {{ request()->routeIs('admin.dashboard') ? 'bg-white bg-opacity-10' : '' }}"
                    href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a class="text-white text-decoration-none px-3 py-2 rounded {{ request()->routeIs('admin.events.index', 'admin.events.create', 'admin.events.edit', 'admin.events.update') ? 'bg-white bg-opacity-10' : '' }}"
                    href="{{ route('admin.events.index') }}">
                    <i class="bi bi-calendar-event me-2"></i> Manajemen Event
                </a>
                <a class="text-white text-decoration-none px-3 py-2 rounded {{ request()->routeIs('admin.events.questions.*') ? 'bg-white bg-opacity-10' : '' }}"
                    href="{{ route('admin.events.index') }}">
                    <i class="bi bi-question-circle me-2"></i> Pertanyaan Formulir
                </a>
                <a class="text-white text-decoration-none px-3 py-2 rounded {{ request()->routeIs('admin.registrations.*') ? 'bg-white bg-opacity-10' : '' }}"
                    href="{{ route('admin.registrations.index') }}">
                    <i class="bi bi-people me-2"></i> Data Pendaftar
                </a>
                <a class="text-white text-decoration-none px-3 py-2 rounded {{ request()->routeIs('admin.categories.*') ? 'bg-white bg-opacity-10' : '' }}"
                    href="{{ route('admin.categories.index') }}">
                    <i class="bi bi-tags me-2"></i> Kategori Event
                </a>
                <a class="text-white text-decoration-none px-3 py-2 rounded {{ request()->routeIs('admin.admins.*') ? 'bg-white bg-opacity-10' : '' }}"
                    href="{{ route('admin.admins.index') }}">
                    <i class="bi bi-person-gear me-2"></i> Akun Admin
                </a>
                <a class="text-white text-decoration-none px-3 py-2 rounded {{ request()->routeIs('admin.settings.*') ? 'bg-white bg-opacity-10' : '' }}"
                    href="{{ route('admin.settings.edit') }}">
                    <i class="bi bi-gear me-2"></i> Pengaturan
                </a>
            </nav>
            <div class="p-3 border-top border-light border-opacity-25">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="btn btn-outline-light w-100" type="submit">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-grow-1 d-flex flex-column">
            <header class="bg-white border-bottom py-3">
                <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-semibold">@yield('page-title', 'Dashboard')</h6>
                        <small class="text-muted">Halo, {{ auth()->user()->name }}</small>
                    </div>
                    <div class="d-lg-none">
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-dark-teal" type="submit">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-grow-1 bg-light">
                <div class="container-fluid px-4 py-4">
                    @include('components.alert')
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>

</html>
