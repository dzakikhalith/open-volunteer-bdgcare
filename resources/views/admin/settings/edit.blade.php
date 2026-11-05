@extends('layouts.admin')

@section('title', 'Pengaturan Website - Bandung Care')
@section('page-title', 'Pengaturan')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Pengaturan Website</h1>
            <p class="text-muted mb-0">Kelola informasi yang ditampilkan pada halaman publik.</p>
        </div>
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-outline-dark-teal">
            <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Website
        </a>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <div class="card card-rounded mb-4">
            <div class="card-body p-4">
                <h5 class="fw-semibold mb-3">Identitas Situs</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="site_name" class="form-label">Nama Situs <span class="text-danger">*</span></label>
                        <input type="text" id="site_name" name="site_name"
                            class="form-control @error('site_name') is-invalid @enderror"
                            value="{{ old('site_name', $settings['site_name']) }}" required>
                        @error('site_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="tagline" class="form-label">Tagline</label>
                        <input type="text" id="tagline" name="tagline"
                            class="form-control @error('tagline') is-invalid @enderror"
                            value="{{ old('tagline', $settings['tagline']) }}">
                        @error('tagline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label">Deskripsi Singkat</label>
                        <textarea id="description" name="description" rows="3"
                            class="form-control @error('description') is-invalid @enderror">{{ old('description', $settings['description']) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-rounded mb-4">
            <div class="card-body p-4">
                <h5 class="fw-semibold mb-3">Hero & Tentang Kami</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="hero_title" class="form-label">Judul Hero</label>
                        <input type="text" id="hero_title" name="hero_title"
                            class="form-control @error('hero_title') is-invalid @enderror"
                            value="{{ old('hero_title', $settings['hero_title']) }}">
                        @error('hero_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="hero_description" class="form-label">Deskripsi Hero</label>
                        <textarea id="hero_description" name="hero_description" rows="2"
                            class="form-control @error('hero_description') is-invalid @enderror">{{ old('hero_description', $settings['hero_description']) }}</textarea>
                        @error('hero_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="about_text" class="form-label">Teks Tentang Kami</label>
                        <textarea id="about_text" name="about_text" rows="4"
                            class="form-control @error('about_text') is-invalid @enderror">{{ old('about_text', $settings['about_text']) }}</textarea>
                        @error('about_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="footer_text" class="form-label">Teks Footer</label>
                        <textarea id="footer_text" name="footer_text" rows="2"
                            class="form-control @error('footer_text') is-invalid @enderror">{{ old('footer_text', $settings['footer_text']) }}</textarea>
                        @error('footer_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-rounded mb-4">
            <div class="card-body p-4">
                <h5 class="fw-semibold mb-3">Kontak</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="contact_email" class="form-label">Email</label>
                        <input type="email" id="contact_email" name="contact_email"
                            class="form-control @error('contact_email') is-invalid @enderror"
                            value="{{ old('contact_email', $settings['contact_email']) }}">
                        @error('contact_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="contact_phone" class="form-label">Telepon / WhatsApp</label>
                        <input type="text" id="contact_phone" name="contact_phone"
                            class="form-control @error('contact_phone') is-invalid @enderror"
                            value="{{ old('contact_phone', $settings['contact_phone']) }}">
                        @error('contact_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="contact_address" class="form-label">Alamat</label>
                        <input type="text" id="contact_address" name="contact_address"
                            class="form-control @error('contact_address') is-invalid @enderror"
                            value="{{ old('contact_address', $settings['contact_address']) }}">
                        @error('contact_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="instagram" class="form-label">Instagram</label>
                        <input type="text" id="instagram" name="instagram"
                            class="form-control @error('instagram') is-invalid @enderror"
                            value="{{ old('instagram', $settings['instagram']) }}">
                        @error('instagram')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-cherry">
            <i class="bi bi-save me-1"></i> Simpan Pengaturan
        </button>
    </form>
@endsection
