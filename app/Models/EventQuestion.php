<?php

namespace App\Models;

use Database\Factories\EventQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventQuestion extends Model
{
    /** @use HasFactory<EventQuestionFactory> */
    use HasFactory;

    public const TYPE_TEXT = 'text';

    public const TYPE_TEXTAREA = 'textarea';

    public const TYPE_EMAIL = 'email';

    public const TYPE_NUMBER = 'number';

    public const TYPE_DATE = 'date';

    public const TYPE_SELECT = 'select';

    public const TYPE_RADIO = 'radio';

    public const TYPE_CHECKBOX = 'checkbox';

    public const TYPE_URL = 'url';

    public const TYPE_FILE = 'file';

    /**
     * Accepted upload extensions and their maximum size in kilobytes.
     */
    public const FILE_MIMES = 'jpg,jpeg,png,webp,pdf,doc,docx';

    public const FILE_MAX_KB = 2048;

    /**
     * All supported question types.
     *
     * @var list<string>
     */
    public const TYPES = [
        self::TYPE_TEXT,
        self::TYPE_TEXTAREA,
        self::TYPE_EMAIL,
        self::TYPE_NUMBER,
        self::TYPE_DATE,
        self::TYPE_SELECT,
        self::TYPE_RADIO,
        self::TYPE_CHECKBOX,
        self::TYPE_URL,
        self::TYPE_FILE,
    ];

    protected $fillable = [
        'event_id',
        'label',
        'field_key',
        'type',
        'placeholder',
        'help_text',
        'is_required',
        'options',
        'validation_rules',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'options' => 'array',
            'validation_rules' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(RegistrationAnswer::class);
    }
}
