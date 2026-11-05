@extends('layouts.admin')

@section('title', 'Edit Admin - Bandung Care')
@section('page-title', 'Edit Admin')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Edit Admin</h1>
            <p class="text-muted mb-0">{{ $admin->name }}</p>
        </div>
        <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-dark-teal">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card card-rounded">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.admins.update', $admin) }}">
                @csrf
                @method('PUT')

                @include('admin.users._form', ['admin' => $admin])

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-cherry">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-light">Batal</a>
                </div>
            </form>

            @unless ($admin->is(auth()->user()))
                <hr class="my-4">

                <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}"
                    onsubmit="return confirm('Hapus akun admin ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Hapus Akun
                    </button>
                </form>
            @endunless
        </div>
    </div>
@endsection
