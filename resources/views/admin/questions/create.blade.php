@extends('layouts.admin')

@section('title', 'Tambah Pertanyaan - Bandung Care')
@section('page-title', 'Tambah Pertanyaan')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Tambah Pertanyaan</h1>
            <p class="text-muted mb-0">{{ $event->title }}</p>
        </div>
        <a href="{{ route('admin.events.questions.index', $event) }}" class="btn btn-outline-dark-teal">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card card-rounded">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.events.questions.store', $event) }}">
                @csrf

                @include('admin.questions._form', ['question' => $question])

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-cherry">
                        <i class="bi bi-save me-1"></i> Simpan Pertanyaan
                    </button>
                    <a href="{{ route('admin.events.questions.index', $event) }}" class="btn btn-light">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
