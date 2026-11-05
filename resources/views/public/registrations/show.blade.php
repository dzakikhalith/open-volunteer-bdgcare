@extends('layouts.public')

@section('content')
    @php
        $statusMessages = [
            'PENDING' => 'Pendaftaran Anda sudah kami terima dan sedang menunggu review panitia.',
            'REVIEWED' => 'Pendaftaran Anda sedang dalam proses review panitia.',
            'ACCEPTED' => 'Selamat! Anda diterima sebagai volunteer pada event ini. Panitia akan menghubungi Anda melalui email atau telepon.',
            'REJECTED' => 'Mohon maaf, pendaftaran Anda belum dapat kami terima pada event ini.',
            'WITHDRAWN' => 'Pendaftaran ini telah dibatalkan.',
        ];
    @endphp

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card card-rounded mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                <div>
                                    <p class="text-muted mb-1">Kode Pendaftaran</p>
                                    <h1 class="h3 fw-bold text-dark-teal mb-2">{{ $registration->registration_code }}</h1>
                                    <p class="mb-0">{{ $registration->event->title }}</p>
                                </div>
                                @include('components.status-badge', ['status' => $registration->status])
                            </div>

                            <div class="alert alert-light border mt-4 mb-0">
                                {{ $statusMessages[$registration->status] ?? 'Status pendaftaran Anda sedang diproses.' }}
                            </div>
                        </div>
                    </div>

                    <div class="card card-rounded mb-4">
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="text-muted small">Nama Pendaftar</div>
                                    <div class="fw-semibold">{{ $registration->full_name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">Tanggal Daftar</div>
                                    <div class="fw-semibold">{{ optional($registration->registered_at)->format('d M Y H:i') }}</div>
                                </div>
                            </div>

                            <p class="text-muted small mb-0 mt-4">
                                <i class="bi bi-shield-lock me-1"></i>
                                Demi keamanan, jawaban formulir dan data kontak tidak ditampilkan di halaman ini.
                                Hubungi panitia jika Anda memerlukan informasi tersebut.
                            </p>
                        </div>
                    </div>

                    <div class="card card-rounded">
                        <div class="card-body p-4">
                            <h5 class="fw-semibold mb-3">Riwayat Status</h5>
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex gap-3 mb-3">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <div>
                                        <div class="fw-semibold">Pendaftaran dikirim</div>
                                        <small class="text-muted">{{ optional($registration->registered_at)->format('d M Y H:i') }}</small>
                                    </div>
                                </li>
                                @foreach ($registration->statusHistories as $history)
                                    <li class="d-flex gap-3 mb-3">
                                        <i class="bi bi-arrow-repeat text-dark-teal"></i>
                                        <div>
                                            <div class="fw-semibold">
                                                @include('components.status-badge', ['status' => $history->new_status])
                                            </div>
                                            <small class="text-muted">{{ $history->created_at->format('d M Y H:i') }}</small>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                        <a href="{{ route('events.show', $registration->event) }}" class="btn btn-outline-dark-teal">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Event
                        </a>
                        <a href="{{ route('registrations.track') }}" class="btn btn-cherry">Cek Kode Lain</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
