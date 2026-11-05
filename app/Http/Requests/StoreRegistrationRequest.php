<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Models\EventQuestion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize the submitted personal data before validating it.
     */
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['full_name', 'phone'] as $field) {
            if (is_string($this->input($field))) {
                $normalized[$field] = trim($this->input($field));
            }
        }

        if (is_string($this->input('email'))) {
            $normalized['email'] = mb_strtolower(trim($this->input('email')));
        }

        $this->merge($normalized);
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

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('registrations', 'email')->where('event_id', $event->getKey()),
            ],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            ...$this->answerRules($event),
        ];
    }

    /**
     * Get friendly attribute names for validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        /** @var Event $event */
        $event = $this->route('event');

        $attributes = [
            'full_name' => 'nama lengkap',
            'email' => 'email',
            'phone' => 'nomor telepon',
        ];

        foreach ($event->activeQuestions()->get() as $question) {
            $attributes['answers.'.$question->field_key] = $question->label;
        }

        return $attributes;
    }

    /**
     * Build validation rules for the event's dynamic questions.
     *
     * @return array<string, array<mixed>>
     */
    private function answerRules(Event $event): array
    {
        $rules = [];

        foreach ($event->activeQuestions()->get() as $question) {
            $key = 'answers.'.$question->field_key;

            $rules[$key] = $this->rulesForQuestion($question);

            if ($question->type === EventQuestion::TYPE_CHECKBOX) {
                $rules[$key.'.*'] = $this->rulesForCheckboxItem($question);
            }
        }

        return $rules;
    }

    /**
     * @return list<ValidationRule|string>
     */
    private function rulesForQuestion(EventQuestion $question): array
    {
        if ($question->type === EventQuestion::TYPE_FILE) {
            return [
                $question->is_required ? 'required' : 'nullable',
                'file',
                'mimes:'.EventQuestion::FILE_MIMES,
                'max:'.EventQuestion::FILE_MAX_KB,
            ];
        }

        $typeRule = match ($question->type) {
            EventQuestion::TYPE_EMAIL => 'email',
            EventQuestion::TYPE_NUMBER => 'numeric',
            EventQuestion::TYPE_DATE => 'date',
            EventQuestion::TYPE_CHECKBOX => 'array',
            EventQuestion::TYPE_URL => 'url',
            default => 'string',
        };

        $rules = [$question->is_required ? 'required' : 'nullable', $typeRule];

        if ($question->is_required && $question->type === EventQuestion::TYPE_CHECKBOX) {
            $rules[] = 'min:1';
        }

        if (in_array($question->type, [EventQuestion::TYPE_SELECT, EventQuestion::TYPE_RADIO], true)) {
            $rules[] = Rule::in($question->options ?? []);
        }

        foreach ($question->validation_rules ?? [] as $rule) {
            if (is_string($rule) && preg_match('/^(min|max):\d+$/', $rule) === 1) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * @return list<ValidationRule|string>
     */
    private function rulesForCheckboxItem(EventQuestion $question): array
    {
        $rules = ['string'];

        if ($question->options) {
            $rules[] = Rule::in($question->options);
        }

        return $rules;
    }
}
