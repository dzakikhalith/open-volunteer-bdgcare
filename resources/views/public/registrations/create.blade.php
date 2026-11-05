@extends('layouts.public')

@section('content')
    <section class="py-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a class="text-dark-teal text-decoration-none" href="{{ route('events.index') }}">Daftar Event</a></li>
                    <li class="breadcrumb-item"><a class="text-dark-teal text-decoration-none" href="{{ route('events.show', $event) }}">{{ $event->title }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Formulir Pendaftaran</li>
                </ol>
            </nav>

            <div class="row g-4">
                <div class="col-lg-8">
                    <form method="POST" action="{{ route('events.register.store', $event) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="card card-rounded mb-4">
                            <div class="card-body p-4">
                                <h1 class="h4 fw-bold text-dark-teal mb-1">Formulir Pendaftaran Volunteer</h1>
                                <p class="text-muted mb-4">{{ $event->title }}</p>

                                <h5 class="fw-semibold mb-3">Data Diri</h5>

                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}"
                                        class="form-control @error('full_name') is-invalid @enderror" required>
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                                            class="form-control @error('email') is-invalid @enderror" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                            class="form-control @error('phone') is-invalid @enderror" required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($event->activeQuestions->isNotEmpty())
                            <div class="card card-rounded mb-4">
                                <div class="card-body p-4">
                                    <h5 class="fw-semibold mb-3">Pertanyaan Tambahan</h5>
                                    @foreach ($event->activeQuestions as $question)
                                        @include('public.registrations._question', ['question' => $question])
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <button type="submit" class="btn btn-cherry">
                                <i class="bi bi-send me-1"></i> Kirim Pendaftaran
                            </button>
                            <a href="{{ route('events.show', $event) }}" class="btn btn-outline-dark-teal">Batal</a>
                        </div>
                    </form>
                </div>

                <div class="col-lg-4">
                    <div class="card card-rounded sticky-top" style="top: 90px;">
                        <div class="card-body p-4">
                            <h5 class="fw-semibold mb-3">Ringkasan Event</h5>
                            <p class="fw-semibold mb-1">{{ $event->title }}</p>
                            @if ($event->category)
                                <span class="badge text-bg-light text-dark-teal mb-3">{{ $event->category->name }}</span>
                            @endif
                            <div class="d-flex flex-column gap-2 text-muted small">
                                <div><i class="bi bi-calendar-event me-2"></i> {{ optional($event->event_start_at)->format('d M Y H:i') }}</div>
                                <div><i class="bi bi-geo-alt me-2"></i> {{ $event->location }}</div>
                                <div><i class="bi bi-people me-2"></i> Sisa kuota: {{ $event->remainingQuota() ?? 'Tidak dibatasi' }}</div>
                                <div><i class="bi bi-clock me-2"></i> Pendaftaran ditutup {{ optional($event->registration_end_at)->format('d M Y H:i') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
