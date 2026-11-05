@extends('layouts.admin')

@section('title', 'Tambah Event - Bandung Care')
@section('page-title', 'Tambah Event')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Tambah Event</h1>
            <p class="text-muted mb-0">Buat kegiatan volunteer baru</p>
        </div>
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-dark-teal">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card card-rounded">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
                @csrf

                @include('admin.events._form', ['event' => $event])

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-cherry">
                        <i class="bi bi-save me-1"></i> Simpan Event
                    </button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-light">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
