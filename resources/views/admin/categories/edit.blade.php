@extends('layouts.admin')

@section('title', 'Edit Kategori - Bandung Care')
@section('page-title', 'Edit Kategori')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Edit Kategori</h1>
            <p class="text-muted mb-0">{{ $category->name }}</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-dark-teal">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card card-rounded">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                @csrf
                @method('PUT')

                @include('admin.categories._form', ['category' => $category])

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-cherry">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Batal</a>
                </div>
            </form>

            @if ($category->events()->withTrashed()->doesntExist())
                <hr class="my-4">

                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                    onsubmit="return confirm('Hapus kategori ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Hapus Kategori
                    </button>
                </form>
            @else
                <div class="alert alert-light border mt-4 mb-0">
                    Kategori ini masih digunakan oleh {{ $category->events()->withTrashed()->count() }} event sehingga tidak dapat dihapus.
                </div>
            @endif
        </div>
    </div>
@endsection
