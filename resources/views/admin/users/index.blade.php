@extends('layouts.admin')

@section('title', 'Akun Admin - Bandung Care')
@section('page-title', 'Akun Admin')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Akun Admin</h1>
            <p class="text-muted mb-0">Kelola akun yang dapat mengakses dashboard</p>
        </div>
        <a href="{{ route('admin.admins.create') }}" class="btn btn-cherry">
            <i class="bi bi-plus-lg me-1"></i> Tambah Admin
        </a>
    </div>

    <div class="card card-rounded mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.admins.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-8">
                    <label for="search" class="form-label">Cari</label>
                    <input type="search" id="search" name="search" class="form-control"
                        placeholder="Nama atau email admin..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-dark-teal flex-grow-1">Cari</button>
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-light" title="Reset filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-rounded">
        <div class="card-body">
            @if ($admins->isEmpty())
                @include('components.empty-state', [
                    'title' => 'Belum ada akun admin',
                    'description' => 'Tambahkan akun admin untuk membantu mengelola kegiatan.',
                ])
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($admins as $admin)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $admin->name }}
                                            @if ($admin->is(auth()->user()))
                                                <span class="badge text-bg-light text-dark-teal">Anda</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $admin->email }}</td>
                                    <td>
                                        @if ($admin->is_active)
                                            <span class="badge text-bg-success">Aktif</span>
                                        @else
                                            <span class="badge text-bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($admin->created_at)->format('d M Y') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('admin.admins.edit', $admin) }}"
                                                class="btn btn-sm btn-outline-dark-teal" title="Edit akun">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @unless ($admin->is(auth()->user()))
                                                <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}"
                                                    onsubmit="return confirm('Hapus akun admin ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="Hapus akun">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $admins->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
