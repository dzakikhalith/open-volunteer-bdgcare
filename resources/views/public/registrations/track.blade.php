@extends('layouts.public')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card card-rounded">
                        <div class="card-body p-4 p-md-5">
                            <h1 class="h3 fw-bold text-dark-teal mb-2">Cek Status Pendaftaran</h1>
                            <p class="text-muted">Masukkan kode pendaftaran yang Anda terima setelah mengirim formulir.</p>

                            <form method="POST" action="{{ route('registrations.lookup') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="registration_code" class="form-label">Kode Pendaftaran</label>
                                    <input type="text" id="registration_code" name="registration_code"
                                        value="{{ old('registration_code') }}"
                                        class="form-control form-control-lg text-uppercase @error('registration_code') is-invalid @enderror"
                                        placeholder="BC-000000-XXXXX" autocomplete="off" required autofocus>
                                    @error('registration_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-cherry w-100">
                                    <i class="bi bi-search me-1"></i> Cek Status
                                </button>
                            </form>

                            <p class="small text-muted mt-3 mb-0">
                                Belum menerima kode? Periksa kembali halaman konfirmasi setelah mendaftar atau hubungi
                                panitia di bandungcare@example.com.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
