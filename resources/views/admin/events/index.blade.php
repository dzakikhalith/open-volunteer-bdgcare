@extends('layouts.admin')

@section('title', 'Manajemen Event - Bandung Care')
@section('page-title', 'Manajemen Event')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Manajemen Event</h1>
            <p class="text-muted mb-0">Kelola kegiatan volunteer Bandung Care</p>
        </div>
        <a href="{{ route('admin.events.create') }}" class="btn btn-cherry">
            <i class="bi bi-plus-lg me-1"></i> Tambah Event
        </a>
    </div>

    <div class="card card-rounded mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.events.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="search" class="form-label">Cari</label>
                    <input type="search" id="search" name="search" class="form-control"
                        placeholder="Nama atau slug event..." value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-3">
                    <label for="category" class="form-label">Kategori</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua status</option>
                        @foreach (\App\Models\Event::RECRUITMENT_STATUSES as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-dark-teal flex-grow-1">Filter</button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-light" title="Reset filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-rounded">
        <div class="card-body">
            @if ($events->isEmpty())
                @include('components.empty-state', [
                    'title' => 'Belum ada event',
                    'description' => 'Tambahkan event pertama untuk mulai menerima pendaftaran volunteer.',
                ])
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Kategori</th>
                                <th>Jadwal</th>
                                <th>Status</th>
                                <th>Tayang</th>
                                <th class="text-center">Pendaftar</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if ($event->poster_path)
                                                <img src="{{ asset('storage/' . $event->poster_path) }}" alt=""
                                                    class="rounded object-fit-cover" width="56" height="56">
                                            @else
                                                <div class="rounded bg-light text-muted d-flex align-items-center justify-content-center"
                                                    style="width: 56px; height: 56px;">
                                                    <i class="bi bi-image"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">{{ $event->title }}</div>
                                                <small class="text-muted">{{ $event->slug }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $event->category?->name ?? '-' }}</td>
                                    <td>
                                        <div>{{ optional($event->event_start_at)->format('d M Y H:i') }}</div>
                                        <small class="text-muted">{{ $event->location }}</small>
                                    </td>
                                    <td>@include('components.status-badge', ['status' => $event->recruitment_status])</td>
                                    <td>
                                        @if ($event->is_published)
                                            <span class="badge text-bg-success">Tayang</span>
                                        @else
                                            <span class="badge text-bg-secondary">Draft</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $event->registrations_count }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('admin.events.questions.index', $event) }}"
                                                class="btn btn-sm btn-outline-secondary" title="Pertanyaan formulir">
                                                <i class="bi bi-question-circle"></i>
                                            </a>
                                            <a href="{{ route('admin.events.edit', $event) }}"
                                                class="btn btn-sm btn-outline-dark-teal" title="Edit event">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                                                onsubmit="return confirm('Hapus event ini? Data pendaftar tetap tersimpan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus event">
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
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
