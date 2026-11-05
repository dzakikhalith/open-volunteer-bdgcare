@extends('layouts.admin')

@section('title', 'Dashboard Admin - Bandung Care')
@section('page-title', 'Dashboard')

@section('content')
    <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-rounded h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Event</p>
                    <h3 class="fw-bold mb-0">{{ $stats['total_events'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-rounded h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Open Recruitment</p>
                    <h3 class="fw-bold mb-0">{{ $stats['open_events'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-rounded h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Pendaftar</p>
                    <h3 class="fw-bold mb-0">{{ $stats['total_registrations'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-rounded h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Menunggu Review</p>
                    <h3 class="fw-bold mb-0">{{ $stats['pending_registrations'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-rounded h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Event Akan Datang</p>
                    <h3 class="fw-bold mb-0">{{ $stats['upcoming_events'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-rounded h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Pendaftar 7 Hari Terakhir</p>
                    <h3 class="fw-bold mb-0">{{ $stats['new_registrations'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-7">
            <div class="card card-rounded h-100">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">Pendaftar per Event</h5>

                    @if ($registrationsPerEvent->isEmpty())
                        @include('components.empty-state', ['title' => 'Belum ada data', 'description' => 'Grafik akan muncul setelah event memiliki pendaftar.'])
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach ($registrationsPerEvent as $event)
                                <div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-truncate me-2">{{ $event->title }}</span>
                                        <span class="fw-semibold">{{ $event->registrations_count }}</span>
                                    </div>
                                    <div class="progress" role="progressbar" aria-label="Pendaftar {{ $event->title }}"
                                        aria-valuenow="{{ $event->registrations_count }}" aria-valuemin="0" aria-valuemax="{{ $maxRegistrations }}"
                                        style="height: 10px;">
                                        <div class="progress-bar bg-dark-teal"
                                            style="width: {{ ($event->registrations_count / $maxRegistrations) * 100 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-rounded h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-semibold mb-0">Event Terbaru</h5>
                        <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-outline-dark-teal">Lihat Semua</a>
                    </div>

                    @if ($recentEvents->isEmpty())
                        @include('components.empty-state', ['title' => 'Belum ada event', 'description' => 'Tambahkan event pertama untuk mulai menerima pendaftaran.'])
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach ($recentEvents as $event)
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $event->title }}</div>
                                        <small class="text-muted">
                                            {{ optional($event->event_start_at)->format('d M Y') }} &middot;
                                            {{ $event->registrations_count }} pendaftar
                                        </small>
                                    </div>
                                    @include('components.status-badge', ['status' => $event->recruitment_status])
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card card-rounded mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-semibold mb-0">Pendaftar Terbaru</h5>
                <a href="{{ route('admin.registrations.index') }}" class="btn btn-sm btn-outline-dark-teal">Lihat Semua</a>
            </div>

            @if ($recentRegistrations->isEmpty())
                @include('components.empty-state', ['title' => 'Belum ada pendaftar', 'description' => 'Pendaftar akan muncul di sini setelah formulir event diisi.'])
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Event</th>
                                <th>Tanggal Daftar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentRegistrations as $registration)
                                <tr>
                                    <td>
                                        <a class="text-dark-teal text-decoration-none fw-semibold"
                                            href="{{ route('admin.registrations.show', $registration) }}">
                                            {{ $registration->registration_code }}
                                        </a>
                                    </td>
                                    <td>{{ $registration->full_name }}</td>
                                    <td>{{ $registration->event->title }}</td>
                                    <td>{{ optional($registration->registered_at)->format('d M Y H:i') }}</td>
                                    <td>@include('components.status-badge', ['status' => $registration->status])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
