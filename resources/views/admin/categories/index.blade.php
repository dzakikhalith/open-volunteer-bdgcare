@extends('layouts.admin')

@section('title', 'Kategori Event - Bandung Care')
@section('page-title', 'Kategori Event')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Kategori Event</h1>
            <p class="text-muted mb-0">Kelompokkan event berdasarkan jenis kegiatan</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-cherry">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
        </a>
    </div>

    <div class="card card-rounded mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.categories.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-8">
                    <label for="search" class="form-label">Cari</label>
                    <input type="search" id="search" name="search" class="form-control"
                        placeholder="Nama atau slug kategori..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-dark-teal flex-grow-1">Cari</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-light" title="Reset filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-rounded">
        <div class="card-body">
            @if ($categories->isEmpty())
                @include('components.empty-state', [
                    'title' => 'Belum ada kategori',
                    'description' => 'Tambahkan kategori untuk mengelompokkan event volunteer.',
                ])
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Slug</th>
                                <th>Deskripsi</th>
                                <th class="text-center">Jumlah Event</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td class="fw-semibold">{{ $category->name }}</td>
                                    <td><code>{{ $category->slug }}</code></td>
                                    <td class="text-muted">{{ $category->description ?? '-' }}</td>
                                    <td class="text-center">{{ $category->events_count }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('admin.categories.edit', $category) }}"
                                                class="btn btn-sm btn-outline-dark-teal" title="Edit kategori">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                                onsubmit="return confirm('Hapus kategori ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Hapus kategori"
                                                    @disabled($category->events_count > 0)>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
