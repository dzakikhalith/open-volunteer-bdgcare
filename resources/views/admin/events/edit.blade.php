@extends('layouts.admin')

@section('title', 'Edit Event - Bandung Care')
@section('page-title', 'Edit Event')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Edit Event</h1>
            <p class="text-muted mb-0">{{ $event->title }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.events.questions.preview', $event) }}" class="btn btn-outline-dark-teal">
                <i class="bi bi-eye me-1"></i> Preview Formulir
            </a>
            @if ($event->is_published)
                <a href="{{ route('events.show', $event) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Event
                </a>
            @endif
            <a href="{{ route('admin.events.questions.index', $event) }}" class="btn btn-outline-dark-teal">
                <i class="bi bi-question-circle me-1"></i> Pertanyaan Formulir
            </a>
            <a href="{{ route('admin.registrations.index', ['event' => $event->id]) }}" class="btn btn-outline-secondary">
                <i class="bi bi-people me-1"></i> Pendaftar
            </a>
            <a href="{{ route('admin.events.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card card-rounded">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @include('admin.events._form', ['event' => $event])

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-cherry">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-light">Batal</a>
                </div>
            </form>

            <hr class="my-4">

            <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                onsubmit="return confirm('Hapus event ini? Data pendaftar tetap tersimpan.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Hapus Event
                </button>
            </form>
        </div>
    </div>
@endsection
