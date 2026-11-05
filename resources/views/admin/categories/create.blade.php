@extends('layouts.admin')

@section('title', 'Tambah Kategori - Bandung Care')
@section('page-title', 'Tambah Kategori')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Tambah Kategori</h1>
            <p class="text-muted mb-0">Buat kategori event baru</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-dark-teal">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card card-rounded">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf

                @include('admin.categories._form', ['category' => $category])

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-cherry">
                        <i class="bi bi-save me-1"></i> Simpan Kategori
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
