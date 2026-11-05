@extends('layouts.admin')

@section('title', 'Preview Formulir - Bandung Care')
@section('page-title', 'Preview Formulir')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Preview Formulir Pendaftaran</h1>
            <p class="text-muted mb-0">{{ $event->title }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.events.questions.index', $event) }}" class="btn btn-outline-dark-teal">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Pertanyaan
            </a>
            @if ($event->is_published)
                <a href="{{ route('events.show', $event) }}" target="_blank" rel="noopener" class="btn btn-light">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Halaman Event
                </a>
            @endif
        </div>
    </div>

    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        Ini hanya preview. Field dinonaktifkan dan data tidak dapat dikirim.
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card card-rounded mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Data Diri</h5>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="Nama lengkap pendaftar" disabled>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" placeholder="email@contoh.com" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" placeholder="08xxxxxxxxxx" disabled>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-rounded">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Pertanyaan Tambahan</h5>

                    @if ($event->activeQuestions->isEmpty())
                        @include('components.empty-state', [
                            'title' => 'Belum ada pertanyaan aktif',
                            'description' => 'Tambahkan pertanyaan agar formulir memiliki isian tambahan.',
                        ])
                    @else
                        <fieldset disabled>
                            @foreach ($event->activeQuestions as $question)
                                @include('public.registrations._question', ['question' => $question])
                            @endforeach
                        </fieldset>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-rounded">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Ringkasan Event</h5>
                    <p class="fw-semibold mb-1">{{ $event->title }}</p>
                    @if ($event->category)
                        <span class="badge text-bg-light text-dark-teal mb-3">{{ $event->category->name }}</span>
                    @endif
                    <div class="d-flex flex-column gap-2 text-muted small">
                        <div><i class="bi bi-calendar-event me-2"></i> {{ optional($event->event_start_at)->format('d M Y H:i') }}</div>
                        <div><i class="bi bi-geo-alt me-2"></i> {{ $event->location }}</div>
                        <div><i class="bi bi-people me-2"></i> Kuota: {{ $event->quota ?? 'Tidak dibatasi' }}</div>
                        <div><i class="bi bi-clock me-2"></i> Pendaftaran ditutup {{ optional($event->registration_end_at)->format('d M Y H:i') ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
