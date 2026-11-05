@php
    $datetime = fn ($value) => $value ? $value->format('Y-m-d\TH:i') : null;
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label for="title" class="form-label">Judul Event <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
            value="{{ old('title', $event->title) }}" required>
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $event->category_id) === $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label for="slug" class="form-label">Slug</label>
        <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror"
            value="{{ old('slug', $event->slug) }}" placeholder="Kosongkan untuk dibuat otomatis dari judul">
        @error('slug')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Digunakan pada URL halaman event publik.</div>
    </div>

    <div class="col-md-4">
        <label for="poster" class="form-label">Poster</label>
        <input type="file" id="poster" name="poster" accept="image/jpeg,image/png,image/webp"
            class="form-control @error('poster') is-invalid @enderror">
        @error('poster')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">JPG, PNG, atau WEBP. Maksimal 2 MB.</div>
    </div>

    @if ($event->poster_path)
        <div class="col-12">
            <img src="{{ asset('storage/' . $event->poster_path) }}" alt="Poster {{ $event->title }}"
                class="rounded border object-fit-cover" width="160" height="90">

            <div class="form-check mt-2">
                <input type="checkbox" id="remove_poster" name="remove_poster" value="1" class="form-check-input"
                    @checked(old('remove_poster'))>
                <label class="form-check-label" for="remove_poster">Hapus poster saat menyimpan</label>
            </div>
        </div>
    @endif

    <div class="col-12">
        <label for="summary" class="form-label">Ringkasan</label>
        <textarea id="summary" name="summary" rows="2" class="form-control @error('summary') is-invalid @enderror"
            maxlength="1000">{{ old('summary', $event->summary) }}</textarea>
        @error('summary')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Deskripsi Kegiatan</label>
        <textarea id="description" name="description" rows="6"
            class="form-control @error('description') is-invalid @enderror">{{ old('description', $event->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="requirements" class="form-label">Tujuan &amp; Persyaratan</label>
        <textarea id="requirements" name="requirements" rows="4"
            class="form-control @error('requirements') is-invalid @enderror">{{ old('requirements', $event->requirements) }}</textarea>
        @error('requirements')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Tulis satu persyaratan per baris.</div>
    </div>

    <div class="col-md-6">
        <label for="location" class="form-label">Lokasi <span class="text-danger">*</span></label>
        <input type="text" id="location" name="location" class="form-control @error('location') is-invalid @enderror"
            value="{{ old('location', $event->location) }}" required>
        @error('location')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="quota" class="form-label">Kuota Volunteer</label>
        <input type="number" id="quota" name="quota" min="1"
            class="form-control @error('quota') is-invalid @enderror" value="{{ old('quota', $event->quota) }}"
            placeholder="Tanpa batas">
        @error('quota')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="recruitment_status" class="form-label">Status Rekrutmen <span class="text-danger">*</span></label>
        <select id="recruitment_status" name="recruitment_status"
            class="form-select @error('recruitment_status') is-invalid @enderror" required>
            @foreach (\App\Models\Event::RECRUITMENT_STATUSES as $status)
                <option value="{{ $status }}" @selected(old('recruitment_status', $event->recruitment_status) === $status)>
                    {{ $status }}
                </option>
            @endforeach
        </select>
        @error('recruitment_status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="event_start_at" class="form-label">Mulai Event <span class="text-danger">*</span></label>
        <input type="datetime-local" id="event_start_at" name="event_start_at"
            class="form-control @error('event_start_at') is-invalid @enderror"
            value="{{ old('event_start_at', $datetime($event->event_start_at)) }}" required>
        @error('event_start_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="event_end_at" class="form-label">Selesai Event</label>
        <input type="datetime-local" id="event_end_at" name="event_end_at"
            class="form-control @error('event_end_at') is-invalid @enderror"
            value="{{ old('event_end_at', $datetime($event->event_end_at)) }}">
        @error('event_end_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="registration_start_at" class="form-label">Mulai Pendaftaran</label>
        <input type="datetime-local" id="registration_start_at" name="registration_start_at"
            class="form-control @error('registration_start_at') is-invalid @enderror"
            value="{{ old('registration_start_at', $datetime($event->registration_start_at)) }}">
        @error('registration_start_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="registration_end_at" class="form-label">Selesai Pendaftaran</label>
        <input type="datetime-local" id="registration_end_at" name="registration_end_at"
            class="form-control @error('registration_end_at') is-invalid @enderror"
            value="{{ old('registration_end_at', $datetime($event->registration_end_at)) }}">
        @error('registration_end_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="checkbox" id="is_published" name="is_published" value="1" class="form-check-input"
                @checked(old('is_published', $event->is_published))>
            <label class="form-check-label" for="is_published">Tayangkan event di halaman publik</label>
        </div>
        @error('is_published')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>
