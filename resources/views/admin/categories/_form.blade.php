<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $category->name) }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="slug" class="form-label">Slug</label>
        <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror"
            value="{{ old('slug', $category->slug) }}" placeholder="Kosongkan untuk dibuat otomatis dari nama">
        @error('slug')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Digunakan untuk filter event pada halaman admin.</div>
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea id="description" name="description" rows="3"
            class="form-control @error('description') is-invalid @enderror"
            maxlength="1000">{{ old('description', $category->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
