@extends('layouts.public')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card card-rounded mb-4">
                        <div class="ratio ratio-16x9">
                            @if ($event->poster_path)
                                <img src="{{ asset('storage/' . $event->poster_path) }}" class="card-img-top object-fit-cover" alt="{{ $event->title }}">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-light text-muted">
                                    <i class="bi bi-image fs-1"></i>
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div>
                                    <h1 class="h3 fw-bold text-dark-teal mb-2">{{ $event->title }}</h1>
                                    @if ($event->category)
                                        <span class="badge text-bg-light text-dark-teal">{{ $event->category->name }}</span>
                                    @endif
                                </div>
                                @include('components.status-badge', ['status' => $event->recruitment_status])
                            </div>
                            <p class="mb-4">{{ $event->summary }}</p>
                            <h5 class="fw-semibold">Deskripsi Kegiatan</h5>
                            <div class="mb-4">{!! nl2br(e($event->description)) !!}</div>
                            <h5 class="fw-semibold">Tujuan & Persyaratan</h5>
                            <div class="mb-0">{!! nl2br(e($event->requirements)) !!}</div>

                            @if ($event->activeQuestions->isNotEmpty())
                                <h5 class="fw-semibold mt-4">Informasi yang Perlu Dipersiapkan</h5>
                                <ul class="mb-0">
                                    @foreach ($event->activeQuestions as $question)
                                        <li>
                                            {{ $question->label }}
                                            @if ($question->is_required)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                                <p class="text-muted small mb-0 mt-2">Daftar di atas akan ditanyakan pada formulir pendaftaran.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-rounded sticky-top" style="top: 90px;">
                        <div class="card-body p-4 d-flex flex-column gap-3">
                            <div>
                                <h5 class="fw-semibold mb-3">Informasi Kegiatan</h5>
                                <div class="d-flex flex-column gap-2 text-muted small">
                                    <div><i class="bi bi-calendar-event me-2"></i> {{ optional($event->event_start_at)->format('d M Y H:i') }}</div>
                                    <div><i class="bi bi-geo-alt me-2"></i> {{ $event->location }}</div>
                                    <div><i class="bi bi-people me-2"></i> Kuota: {{ $event->quota ?? 'Tidak dibatasi' }}</div>
                                    <div><i class="bi bi-clock me-2"></i> Pendaftaran: {{ optional($event->registration_start_at)->format('d M Y H:i') ?? 'Kapan saja' }} - {{ optional($event->registration_end_at)->format('d M Y H:i') ?? 'Kapan saja' }}</div>
                                </div>
                            </div>
                            <div class="d-grid">
                                @if ($isRegistrationOpen)
                                    <a href="{{ route('events.register', $event) }}" class="btn btn-cherry">
                                        <i class="bi bi-pencil-square me-1"></i> Daftar Menjadi Volunteer
                                    </a>
                                    <small class="text-muted text-center mt-2">
                                        Sisa kuota: {{ $remainingQuota ?? 'Tidak dibatasi' }}
                                    </small>
                                @else
                                    <button class="btn btn-cherry" disabled>Daftar Menjadi Volunteer</button>
                                    <small class="text-muted text-center mt-2">Pendaftaran belum dibuka atau sudah ditutup</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

