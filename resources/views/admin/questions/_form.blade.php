@php
    $typeLabels = [
        'text' => 'Text (satu baris)',
        'textarea' => 'Textarea (paragraf)',
        'email' => 'Email',
        'number' => 'Angka',
        'date' => 'Tanggal',
        'select' => 'Dropdown',
        'radio' => 'Radio Button',
        'checkbox' => 'Checkbox (pilihan ganda)',
        'url' => 'URL / Tautan',
        'file' => 'Upload File',
    ];

    $validationRules = $question->validation_rules ?? [];
    $minLength = collect($validationRules)->first(fn ($rule) => str_starts_with((string) $rule, 'min:'));
    $maxLength = collect($validationRules)->first(fn ($rule) => str_starts_with((string) $rule, 'max:'));
    $minLength = $minLength ? (int) str_replace('min:', '', $minLength) : null;
    $maxLength = $maxLength ? (int) str_replace('max:', '', $maxLength) : null;
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label for="label" class="form-label">Label Pertanyaan <span class="text-danger">*</span></label>
        <input type="text" id="label" name="label" class="form-control @error('label') is-invalid @enderror"
            value="{{ old('label', $question->label) }}" required>
        @error('label')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="type" class="form-label">Tipe Pertanyaan <span class="text-danger">*</span></label>
        <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach (\App\Models\EventQuestion::TYPES as $type)
                <option value="{{ $type }}" @selected(old('type', $question->type) === $type)>
                    {{ $typeLabels[$type] ?? $type }}
                </option>
            @endforeach
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="field_key" class="form-label">Kunci Field</label>
        <input type="text" id="field_key" name="field_key"
            class="form-control @error('field_key') is-invalid @enderror"
            value="{{ old('field_key', $question->field_key) }}" placeholder="Kosongkan untuk dibuat otomatis dari label">
        @error('field_key')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Huruf kecil, angka, dan garis bawah. Contoh: motivasi_bergabung.</div>
    </div>

    <div class="col-md-3">
        <label for="sort_order" class="form-label">Urutan</label>
        <input type="number" id="sort_order" name="sort_order" min="0"
            class="form-control @error('sort_order') is-invalid @enderror"
            value="{{ old('sort_order', $question->sort_order) }}">
        @error('sort_order')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 d-flex flex-column gap-2 pt-md-4">
        <div class="form-check form-switch">
            <input type="checkbox" id="is_required" name="is_required" value="1" class="form-check-input"
                @checked(old('is_required', $question->is_required))>
            <label class="form-check-label" for="is_required">Wajib diisi</label>
        </div>
        <div class="form-check form-switch">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                @checked(old('is_active', $question->is_active ?? true))>
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>

    <div class="col-md-6">
        <label for="placeholder" class="form-label">Placeholder</label>
        <input type="text" id="placeholder" name="placeholder"
            class="form-control @error('placeholder') is-invalid @enderror"
            value="{{ old('placeholder', $question->placeholder) }}">
        @error('placeholder')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="help_text" class="form-label">Teks Bantuan</label>
        <input type="text" id="help_text" name="help_text"
            class="form-control @error('help_text') is-invalid @enderror"
            value="{{ old('help_text', $question->help_text) }}">
        @error('help_text')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="min_length" class="form-label">Panjang Minimal</label>
        <input type="number" id="min_length" name="min_length" min="0"
            class="form-control @error('min_length') is-invalid @enderror"
            value="{{ old('min_length', $minLength) }}" placeholder="Tanpa batas">
        @error('min_length')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="max_length" class="form-label">Panjang Maksimal</label>
        <input type="number" id="max_length" name="max_length" min="0"
            class="form-control @error('max_length') is-invalid @enderror"
            value="{{ old('max_length', $maxLength) }}" placeholder="Tanpa batas">
        @error('max_length')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="options_text" class="form-label">Pilihan Jawaban</label>
        <textarea id="options_text" name="options_text" rows="5"
            class="form-control @error('options') is-invalid @enderror"
            placeholder="Satu pilihan per baris">{{ old('options_text', implode("\n", $question->options ?? [])) }}</textarea>
        @error('options')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @error('options.*')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
        <div class="form-text">Wajib diisi untuk tipe Dropdown, Radio Button, dan Checkbox.</div>
    </div>
</div>
