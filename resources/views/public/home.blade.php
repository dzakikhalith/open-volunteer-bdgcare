@extends('layouts.public')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <h1 class="display-5 fw-bold text-dark-teal">{{ $siteSettings['hero_title'] }}</h1>
                    <p class="lead mt-3">{{ $siteSettings['hero_description'] }}</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="{{ route('events.index') }}" class="btn btn-cherry">Lihat Event</a>
                        <a href="#tentang" class="btn btn-outline-dark-teal">Tentang Kami</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card card-rounded overflow-hidden">
                        <div class="ratio ratio-16x9 bg-light d-flex align-items-center justify-content-center">
                            <i class="bi bi-people-fill text-dark-teal" style="font-size: 4rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="tentang" class="py-5 section-light">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h2 class="fw-bold text-dark-teal mb-3">Pengenalan {{ $siteSettings['site_name'] }}</h2>
                    <p class="mb-0">{{ $siteSettings['about_text'] }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark-teal mb-1">Event yang Sedang Membuka Rekrutmen</h2>
                    <p class="text-muted mb-0">Daftar sekarang dan jadi bagian dari perubahan</p>
                </div>
                <a href="{{ route('events.index') }}" class="btn btn-outline-dark-teal d-none d-md-inline-block">Lihat Semua</a>
            </div>
            @if ($events->isNotEmpty())
                <div class="row g-4">
                    @foreach ($events as $event)
                        <div class="col-md-6 col-lg-4">
                            @include('components.event-card', ['event' => $event])
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-4 d-md-none">
                    <a href="{{ route('events.index') }}" class="btn btn-outline-dark-teal">Lihat Semua Event</a>
                </div>
            @else
                @include('components.empty-state', ['title' => 'Belum ada rekrutmen yang tersedia', 'description' => 'Saat ini belum ada event yang sedang membuka rekrutmen.'])
            @endif
        </div>
    </section>

    <section class="py-5 section-light">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h2 class="fw-bold text-dark-teal mb-3">Cara Menjadi Volunteer</h2>
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <div class="card card-rounded h-100">
                                <div class="card-body">
                                    <div class="text-dark-teal mb-2"><i class="bi bi-search fs-3"></i></div>
                                    <h6 class="fw-semibold">Cari Event</h6>
                                    <p class="small text-muted mb-0">Temukan event yang sesuai minat kamu</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-rounded h-100">
                                <div class="card-body">
                                    <div class="text-dark-teal mb-2"><i class="bi bi-pencil-square fs-3"></i></div>
                                    <h6 class="fw-semibold">Daftar Online</h6>
                                    <p class="small text-muted mb-0">Isi formulir tanpa perlu membuat akun</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-rounded h-100">
                                <div class="card-body">
                                    <div class="text-dark-teal mb-2"><i class="bi bi-heart fs-3"></i></div>
                                    <h6 class="fw-semibold">Berkontribusi</h6>
                                    <p class="small text-muted mb-0">Ikuti kegiatan dan buat dampak nyata</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="card card-rounded bg-dark-teal text-white">
                <div class="card-body text-center p-5">
                    <h2 class="fw-bold mb-3">Siap Jadi Bagian dari Perubahan?</h2>
                    <p class="mb-4">Gabung bersama volunteer {{ $siteSettings['site_name'] }} untuk masyarakat yang lebih peduli.</p>
                    <a href="{{ route('events.index') }}" class="btn btn-lime">Lihat Event Tersedia</a>
                </div>
            </div>
        </div>
    </section>
@endsection
