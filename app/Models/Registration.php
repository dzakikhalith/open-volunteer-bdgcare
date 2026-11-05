<?php

namespace App\Models;

use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_REVIEWED = 'REVIEWED';

    public const STATUS_ACCEPTED = 'ACCEPTED';

    public const STATUS_REJECTED = 'REJECTED';

    public const STATUS_WITHDRAWN = 'WITHDRAWN';

    /**
     * All available registration statuses.
     *
     * @var list<string>
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_REVIEWED,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
        self::STATUS_WITHDRAWN,
    ];

    /**
     * Statuses that still occupy a quota slot.
     *
     * @var list<string>
     */
    public const ACTIVE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_REVIEWED,
        self::STATUS_ACCEPTED,
    ];

    protected $fillable = [
        'event_id',
        'registration_code',
        'full_name',
        'email',
        'phone',
        'status',
        'registered_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'reviewed_at' => 'datetime',
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

    public function statusHistories(): HasMany
    {
        return $this->hasMany(RegistrationStatusHistory::class);
    }

    public function getRouteKeyName(): string
    {
        return 'registration_code';
    }

    /**
     * Build a human readable, unique registration code.
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = 'BC-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (static::query()->where('registration_code', $code)->exists());

        return $code;
    }
}
