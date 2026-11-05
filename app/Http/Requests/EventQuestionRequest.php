<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Models\EventQuestion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Normalize the input before validating it.
     */
    protected function prepareForValidation(): void
    {
        $options = collect(preg_split('/\r\n|\r|\n/', (string) $this->input('options_text')) ?: [])
            ->map(fn (string $option): string => trim($option))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $fieldKey = $this->filled('field_key')
            ? Str::slug((string) $this->input('field_key'), '_')
            : Str::slug((string) $this->input('label'), '_');

        $validationRules = [];

        if ($this->filled('min_length')) {
            $validationRules[] = 'min:'.(int) $this->input('min_length');
        }

        if ($this->filled('max_length')) {
            $validationRules[] = 'max:'.(int) $this->input('max_length');
        }

        $this->merge([
            'field_key' => $fieldKey,
            'options' => $options === [] ? null : $options,
            'validation_rules' => $validationRules === [] ? null : $validationRules,
            'is_required' => $this->boolean('is_required'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Event $event */
        $event = $this->route('event');
        /** @var EventQuestion|null $question */
        $question = $this->route('question');

        $requiresOptions = in_array($this->input('type'), [
            EventQuestion::TYPE_SELECT,
            EventQuestion::TYPE_RADIO,
            EventQuestion::TYPE_CHECKBOX,
        ], true);

        return [
            'label' => ['required', 'string', 'max:255'],
            'field_key' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('event_questions', 'field_key')
                    ->where('event_id', $event->getKey())
                    ->ignore($question?->getKey()),
            ],
            'type' => ['required', Rule::in(EventQuestion::TYPES)],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'help_text' => ['nullable', 'string', 'max:1000'],
            'is_required' => ['required', 'boolean'],
            'options' => $requiresOptions ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'options.*' => ['string', 'max:255'],
            'min_length' => ['nullable', 'integer', 'min:0'],
            'max_length' => ['nullable', 'integer', 'min:0', ...($this->filled('min_length') ? ['gte:min_length'] : [])],
            'validation_rules' => ['nullable', 'array'],
            'validation_rules.*' => ['string', 'regex:/^(min|max):\d+$/'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Get friendly attribute names for validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'field_key' => 'kunci field',
            'type' => 'tipe pertanyaan',
            'options' => 'pilihan jawaban',
            'min_length' => 'panjang minimal',
            'max_length' => 'panjang maksimal',
            'sort_order' => 'urutan',
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'options.required' => 'Pilihan jawaban wajib diisi untuk tipe pertanyaan pilihan.',
            'max_length.gte' => 'Panjang maksimal tidak boleh lebih kecil dari panjang minimal.',
            'validation_rules.*.regex' => 'Aturan validasi hanya boleh berisi batas min atau max yang aman.',
        ];
    }
}
