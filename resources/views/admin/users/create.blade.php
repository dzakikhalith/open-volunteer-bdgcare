@extends('layouts.admin')

@section('title', 'Tambah Admin - Bandung Care')
@section('page-title', 'Tambah Admin')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Tambah Admin</h1>
            <p class="text-muted mb-0">Buat akun baru dengan akses dashboard</p>
        </div>
        <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-dark-teal">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card card-rounded">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.admins.store') }}">
                @csrf

                @include('admin.users._form', ['admin' => $admin])

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-cherry">
                        <i class="bi bi-save me-1"></i> Simpan Akun
                    </button>
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-light">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
