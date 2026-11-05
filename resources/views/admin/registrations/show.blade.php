@extends('layouts.admin')

@section('title', 'Detail Pendaftar - Bandung Care')
@section('page-title', 'Detail Pendaftar')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">{{ $registration->registration_code }}</h1>
            <p class="text-muted mb-0">{{ $registration->full_name }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.registrations.index') }}" class="btn btn-outline-dark-teal">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('admin.events.edit', $registration->event) }}" class="btn btn-light">
                <i class="bi bi-calendar-event me-1"></i> Lihat Event
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card card-rounded mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                        <h5 class="fw-semibold mb-0">Data Pendaftar</h5>
                        @include('components.status-badge', ['status' => $registration->status])
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Nama Lengkap</div>
                            <div class="fw-semibold">{{ $registration->full_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Email</div>
                            <div class="fw-semibold">{{ $registration->email }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Nomor Telepon</div>
                            <div class="fw-semibold">{{ $registration->phone }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Tanggal Daftar</div>
                            <div class="fw-semibold">{{ optional($registration->registered_at)->format('d M Y H:i') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Terakhir Direview</div>
                            <div class="fw-semibold">{{ optional($registration->reviewed_at)->format('d M Y H:i') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Event</div>
                            <div class="fw-semibold">{{ $registration->event?->title ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-rounded mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Jawaban Formulir</h5>

                    @if ($registration->answers->isEmpty())
                        <p class="text-muted mb-0">Pendaftar tidak mengisi pertanyaan tambahan.</p>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach ($registration->answers as $answer)
                                <div>
                                    <div class="text-muted small">{{ $answer->question_label_snapshot }}</div>
                                    <div>
                                        @if ($answer->question_type_snapshot === \App\Models\EventQuestion::TYPE_FILE)
                                            @if ($answer->answer_text)
                                                <a href="{{ route('admin.registrations.answers.download', [$registration, $answer]) }}"
                                                    class="btn btn-sm btn-outline-dark-teal">
                                                    <i class="bi bi-download me-1"></i> Unduh Berkas
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        @elseif (is_array($answer->answer_json))
                                            {{ implode(', ', $answer->answer_json) }}
                                        @elseif ($answer->answer_text)
                                            {!! nl2br(e($answer->answer_text)) !!}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
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
                                    @if ($history->notes)
                                        <div class="small">{{ $history->notes }}</div>
                                    @endif
                                    <small class="text-muted">
                                        {{ $history->created_at?->format('d M Y H:i') }}
                                        @if ($history->changedBy)
                                            &middot; oleh {{ $history->changedBy->name }}
                                        @endif
                                    </small>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-rounded mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Informasi Event</h5>
                    <div class="d-flex flex-column gap-2 text-muted small">
                        <div><i class="bi bi-calendar-event me-2"></i> {{ optional($registration->event?->event_start_at)->format('d M Y H:i') }}</div>
                        <div><i class="bi bi-geo-alt me-2"></i> {{ $registration->event?->location ?? '-' }}</div>
                        <div><i class="bi bi-tags me-2"></i> {{ $registration->event?->category?->name ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="card card-rounded">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Ubah Status</h5>

                    <form method="POST" action="{{ route('admin.registrations.update-status', $registration) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach (\App\Models\Registration::STATUSES as $status)
                                    <option value="{{ $status }}" @selected(old('status', $registration->status) === $status)>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea id="notes" name="notes" rows="3"
                                class="form-control @error('notes') is-invalid @enderror"
                                placeholder="Catatan untuk perubahan status (opsional)">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-cherry w-100">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
