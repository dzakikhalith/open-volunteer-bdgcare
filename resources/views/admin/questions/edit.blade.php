@extends('layouts.admin')

@section('title', 'Edit Pertanyaan - Bandung Care')
@section('page-title', 'Edit Pertanyaan')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Edit Pertanyaan</h1>
            <p class="text-muted mb-0">{{ $event->title }}</p>
        </div>
        <a href="{{ route('admin.events.questions.index', $event) }}" class="btn btn-outline-dark-teal">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card card-rounded">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.events.questions.update', [$event, $question]) }}">
                @csrf
                @method('PUT')

                @include('admin.questions._form', ['question' => $question])

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-cherry">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.events.questions.index', $event) }}" class="btn btn-light">Batal</a>
                </div>
            </form>

            <hr class="my-4">

            <form method="POST" action="{{ route('admin.events.questions.destroy', [$event, $question]) }}"
                onsubmit="return confirm('Hapus pertanyaan ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Hapus Pertanyaan
                </button>
            </form>
        </div>
    </div>
@endsection
