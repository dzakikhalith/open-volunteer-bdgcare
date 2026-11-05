@php
    $fieldKey = $question->field_key;
    $errorKey = 'answers.'.$fieldKey;
    $oldValue = old($errorKey);
    $options = $question->options ?? [];
@endphp

<div class="mb-3">
    <label class="form-label"
        @if (! in_array($question->type, [\App\Models\EventQuestion::TYPE_RADIO, \App\Models\EventQuestion::TYPE_CHECKBOX], true))
            for="question-{{ $fieldKey }}"
        @endif>
        {{ $question->label }}
        @if ($question->is_required)
            <span class="text-danger">*</span>
        @endif
    </label>

    @switch($question->type)
        @case(\App\Models\EventQuestion::TYPE_TEXTAREA)
            <textarea id="question-{{ $fieldKey }}" name="answers[{{ $fieldKey }}]" rows="4"
                class="form-control @error($errorKey) is-invalid @enderror"
                placeholder="{{ $question->placeholder }}"
                @if ($question->is_required) required @endif>{{ $oldValue }}</textarea>
            @break

        @case(\App\Models\EventQuestion::TYPE_SELECT)
            <select id="question-{{ $fieldKey }}" name="answers[{{ $fieldKey }}]"
                class="form-select @error($errorKey) is-invalid @enderror"
                @if ($question->is_required) required @endif>
                <option value="">Pilih {{ $question->label }}</option>
                @foreach ($options as $option)
                    <option value="{{ $option }}" @selected((string) $oldValue === (string) $option)>{{ $option }}</option>
                @endforeach
            </select>
            @break

        @case(\App\Models\EventQuestion::TYPE_RADIO)
            <div class="d-flex flex-column gap-2">
                @foreach ($options as $option)
                    <div class="form-check">
                        <input class="form-check-input @error($errorKey) is-invalid @enderror" type="radio"
                            id="question-{{ $fieldKey }}-{{ $loop->index }}" name="answers[{{ $fieldKey }}]"
                            value="{{ $option }}" @checked((string) $oldValue === (string) $option)
                            @if ($question->is_required) required @endif>
                        <label class="form-check-label" for="question-{{ $fieldKey }}-{{ $loop->index }}">{{ $option }}</label>
                    </div>
                @endforeach
            </div>
            @break

        @case(\App\Models\EventQuestion::TYPE_CHECKBOX)
            <div class="d-flex flex-column gap-2">
                @foreach ($options as $option)
                    <div class="form-check">
                        <input class="form-check-input @error($errorKey) is-invalid @enderror" type="checkbox"
                            id="question-{{ $fieldKey }}-{{ $loop->index }}" name="answers[{{ $fieldKey }}][]"
                            value="{{ $option }}" @checked(in_array($option, (array) $oldValue, true))>
                        <label class="form-check-label" for="question-{{ $fieldKey }}-{{ $loop->index }}">{{ $option }}</label>
                    </div>
                @endforeach
            </div>
            @break

        @case(\App\Models\EventQuestion::TYPE_DATE)
            <input type="date" id="question-{{ $fieldKey }}" name="answers[{{ $fieldKey }}]"
                value="{{ $oldValue }}" class="form-control @error($errorKey) is-invalid @enderror"
                @if ($question->is_required) required @endif>
            @break

        @case(\App\Models\EventQuestion::TYPE_NUMBER)
            <input type="number" step="any" id="question-{{ $fieldKey }}" name="answers[{{ $fieldKey }}]"
                value="{{ $oldValue }}" class="form-control @error($errorKey) is-invalid @enderror"
                placeholder="{{ $question->placeholder }}"
                @if ($question->is_required) required @endif>
            @break

        @case(\App\Models\EventQuestion::TYPE_EMAIL)
            <input type="email" id="question-{{ $fieldKey }}" name="answers[{{ $fieldKey }}]"
                value="{{ $oldValue }}" class="form-control @error($errorKey) is-invalid @enderror"
                placeholder="{{ $question->placeholder }}"
                @if ($question->is_required) required @endif>
            @break

        @case(\App\Models\EventQuestion::TYPE_URL)
            <input type="url" id="question-{{ $fieldKey }}" name="answers[{{ $fieldKey }}]"
                value="{{ $oldValue }}" class="form-control @error($errorKey) is-invalid @enderror"
                placeholder="{{ $question->placeholder ?: 'https://' }}"
                @if ($question->is_required) required @endif>
            @break

        @case(\App\Models\EventQuestion::TYPE_FILE)
            <input type="file" id="question-{{ $fieldKey }}" name="answers[{{ $fieldKey }}]"
                class="form-control @error($errorKey) is-invalid @enderror"
                accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx"
                @if ($question->is_required) required @endif>
            <div class="form-text">
                {{ $question->help_text ?: 'Format: JPG, PNG, WEBP, PDF, DOC, DOCX. Ukuran maksimal 2 MB.' }}
            </div>
            @break

        @default
            <input type="text" id="question-{{ $fieldKey }}" name="answers[{{ $fieldKey }}]"
                value="{{ $oldValue }}" class="form-control @error($errorKey) is-invalid @enderror"
                placeholder="{{ $question->placeholder }}"
                @if ($question->is_required) required @endif>
    @endswitch

    @if ($question->help_text && $question->type !== \App\Models\EventQuestion::TYPE_FILE)
        <div class="form-text">{{ $question->help_text }}</div>
    @endif

    @error($errorKey)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
