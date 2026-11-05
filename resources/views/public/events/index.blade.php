@extends('layouts.public')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h1 class="h2 fw-bold text-dark-teal mb-1">Daftar Event</h1>
                    <p class="text-muted mb-0">Temukan kegiatan volunteer Bandung Care</p>
                </div>
            </div>

            <div class="card card-rounded mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('events.index') }}" class="row g-2 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label for="search" class="form-label">Cari Event</label>
                            <input type="search" id="search" name="search" class="form-control"
                                placeholder="Cari nama event..." value="{{ request('search') }}">
                        </div>
                        <div class="col-6 col-lg-2">
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
                        <div class="col-6 col-lg-2">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">Semua status</option>
                                @foreach (['SCHEDULED' => 'Segera Dibuka', 'OPEN' => 'Open Recruitment', 'CLOSED' => 'Ditutup', 'COMPLETED' => 'Selesai'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label for="sort" class="form-label">Urutkan</label>
                            <select id="sort" name="sort" class="form-select">
                                <option value="date_desc" @selected(request('sort', 'date_desc') === 'date_desc')>Tanggal terbaru</option>
                                <option value="date_asc" @selected(request('sort') === 'date_asc')>Tanggal terlama</option>
                                <option value="title_asc" @selected(request('sort') === 'title_asc')>Nama A-Z</option>
                                <option value="title_desc" @selected(request('sort') === 'title_desc')>Nama Z-A</option>
                            </select>
                        </div>
                        <div class="col-6 col-lg-2 d-flex gap-2">
                            <button class="btn btn-outline-dark-teal flex-grow-1" type="submit">Filter</button>
                            <a href="{{ route('events.index') }}" class="btn btn-light" title="Reset filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if ($events->count())
                <div class="row g-4">
                    @foreach ($events as $event)
                        <div class="col-md-6 col-lg-4">
                            @include('components.event-card', ['event' => $event])
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    {{ $events->links() }}
                </div>
            @else
                @include('components.empty-state', ['title' => 'Belum ada event', 'description' => 'Tidak ada event yang cocok dengan pencarian atau filter Anda.'])
            @endif
        </div>
    </section>
@endsection
