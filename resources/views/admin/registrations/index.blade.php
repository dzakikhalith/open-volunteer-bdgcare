@extends('layouts.admin')

@section('title', 'Data Pendaftar - Bandung Care')
@section('page-title', 'Data Pendaftar')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Data Pendaftar</h1>
            <p class="text-muted mb-0">Tinjau dan kelola pendaftaran volunteer</p>
        </div>
        <a href="{{ route('admin.registrations.export', request()->only(['search', 'event', 'status', 'date_from', 'date_to'])) }}"
            class="btn btn-outline-dark-teal">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </div>

    <div class="card card-rounded mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.registrations.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="search" class="form-label">Cari</label>
                    <input type="search" id="search" name="search" class="form-control"
                        placeholder="Nama, email, atau kode..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label for="event" class="form-label">Event</label>
                    <select id="event" name="event" class="form-select">
                        <option value="">Semua event</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" @selected((int) request('event') === $event->id)>
                                {{ $event->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua status</option>
                        @foreach (\App\Models\Registration::STATUSES as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="date_from" class="form-label">Dari Tanggal</label>
                    <input type="date" id="date_from" name="date_from" class="form-control @error('date_from') is-invalid @enderror"
                        value="{{ request('date_from') }}">
                    @error('date_from')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6 col-md-2">
                    <label for="date_to" class="form-label">Sampai Tanggal</label>
                    <input type="date" id="date_to" name="date_to" class="form-control @error('date_to') is-invalid @enderror"
                        value="{{ request('date_to') }}">
                    @error('date_to')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-dark-teal flex-grow-1">Filter</button>
                    <a href="{{ route('admin.registrations.index') }}" class="btn btn-light" title="Reset filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-rounded">
        <div class="card-body">
            @if ($registrations->isEmpty())
                @include('components.empty-state', [
                    'title' => 'Belum ada pendaftar',
                    'description' => 'Pendaftar akan muncul di sini setelah formulir event diisi.',
                ])
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Pendaftar</th>
                                <th>Event</th>
                                <th>Tanggal Daftar</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($registrations as $registration)
                                <tr>
                                    <td>
                                        <a class="text-dark-teal text-decoration-none fw-semibold"
                                            href="{{ route('admin.registrations.show', $registration) }}">
                                            {{ $registration->registration_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $registration->full_name }}</div>
                                        <small class="text-muted">{{ $registration->email }}</small>
                                    </td>
                                    <td>{{ $registration->event?->title ?? '-' }}</td>
                                    <td>{{ optional($registration->registered_at)->format('d M Y H:i') }}</td>
                                    <td>@include('components.status-badge', ['status' => $registration->status])</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.registrations.show', $registration) }}"
                                            class="btn btn-sm btn-outline-dark-teal">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $registrations->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
