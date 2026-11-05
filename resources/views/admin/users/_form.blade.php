<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $admin->name) }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $admin->email) }}" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">
            Password
            @unless ($admin->exists)
                <span class="text-danger">*</span>
            @endunless
        </label>
        <input type="password" id="password" name="password"
            class="form-control @error('password') is-invalid @enderror" autocomplete="new-password"
            @unless ($admin->exists) required @endunless>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">
            @if ($admin->exists)
                Kosongkan jika tidak ingin mengubah password. Minimal 8 karakter.
            @else
                Minimal 8 karakter.
            @endif
        </div>
    </div>

    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
            autocomplete="new-password" @unless ($admin->exists) required @endunless>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                @checked(old('is_active', $admin->is_active ?? true))>
            <label class="form-check-label" for="is_active">Akun aktif dan dapat login</label>
        </div>
        @error('is_active')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>
