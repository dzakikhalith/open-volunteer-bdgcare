<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
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
        /** @var Event|null $event */
        $event = $this->route('event');

        $slug = $this->filled('slug')
            ? Str::slug((string) $this->input('slug'))
            : Event::generateUniqueSlug((string) $this->input('title'), $event?->getKey());

        $this->merge([
            'slug' => $slug,
            'is_published' => $this->boolean('is_published'),
            'remove_poster' => $this->boolean('remove_poster'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Event|null $event */
        $event = $this->route('event');

        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('events', 'slug')->ignore($event?->getKey()),
            ],
            'summary' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_poster' => ['nullable', 'boolean'],
            'location' => ['required', 'string', 'max:255'],
            'event_start_at' => ['required', 'date'],
            'event_end_at' => ['nullable', 'date', 'after_or_equal:event_start_at'],
            'registration_start_at' => ['nullable', 'date'],
            'registration_end_at' => ['nullable', 'date', 'after_or_equal:registration_start_at'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'requirements' => ['nullable', 'string'],
            'recruitment_status' => ['required', Rule::in(Event::RECRUITMENT_STATUSES)],
            'is_published' => ['required', 'boolean'],
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
            'category_id' => 'kategori',
            'event_start_at' => 'waktu mulai event',
            'event_end_at' => 'waktu selesai event',
            'registration_start_at' => 'waktu mulai pendaftaran',
            'registration_end_at' => 'waktu selesai pendaftaran',
            'recruitment_status' => 'status rekrutmen',
            'is_published' => 'status tayang',
        ];
    }
}
