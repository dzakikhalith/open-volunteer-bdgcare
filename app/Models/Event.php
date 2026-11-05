<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Available recruitment statuses.
     *
     * @var list<string>
     */
    public const RECRUITMENT_STATUSES = ['DRAFT', 'SCHEDULED', 'OPEN', 'CLOSED', 'COMPLETED'];

    protected $fillable = [
        'category_id',
        'created_by',
        'title',
        'slug',
        'summary',
        'description',
        'poster_path',
        'location',
        'event_start_at',
        'event_end_at',
        'registration_start_at',
        'registration_end_at',
        'quota',
        'requirements',
        'recruitment_status',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'event_start_at' => 'datetime',
            'event_end_at' => 'datetime',
            'registration_start_at' => 'datetime',
            'registration_end_at' => 'datetime',
            'quota' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(EventQuestion::class)->orderBy('sort_order');
    }

    public function activeQuestions(): HasMany
    {
        return $this->hasMany(EventQuestion::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Limit the query to events that are visible on the public site.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->whereNotNull('slug');
    }

    /**
     * Limit the query to events whose registration window is currently open.
     */
    public function scopeOpenForRegistration(Builder $query): void
    {
        $query->published()
            ->where('recruitment_status', 'OPEN')
            ->where(function (Builder $query) {
                $query->whereNull('registration_start_at')
                    ->orWhere('registration_start_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('registration_end_at')
                    ->orWhere('registration_end_at', '>=', now());
            })
            ->where(function (Builder $query) {
                $placeholders = implode(', ', array_fill(0, count(Registration::ACTIVE_STATUSES), '?'));

                $query->whereNull('quota')->orWhereRaw(
                    '(select count(*) from registrations where registrations.event_id = events.id'
                    .' and registrations.status in ('.$placeholders.')) < events.quota',
                    Registration::ACTIVE_STATUSES,
                );
            });
    }

    /**
     * Build a unique slug for the given title.
     */
    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'event';
        $slug = $base;
        $suffix = 2;

        while (static::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Determine whether volunteers may currently submit a registration.
     */
    public function isRegistrationOpen(): bool
    {
        if (! $this->is_published || $this->recruitment_status !== 'OPEN') {
            return false;
        }

        if ($this->registration_start_at && $this->registration_start_at->isFuture()) {
            return false;
        }

        if ($this->registration_end_at && $this->registration_end_at->isPast()) {
            return false;
        }

        return ! $this->isQuotaFull();
    }

    /**
     * Determine whether every volunteer slot has already been taken.
     */
    public function isQuotaFull(): bool
    {
        $remainingQuota = $this->remainingQuota();

        return $remainingQuota !== null && $remainingQuota <= 0;
    }

    /**
     * Get the remaining volunteer slots, or null when the event has no quota.
     */
    public function remainingQuota(): ?int
    {
        if ($this->quota === null) {
            return null;
        }

        $usedQuota = $this->registrations()
            ->whereIn('status', Registration::ACTIVE_STATUSES)
            ->count();

        return max(0, $this->quota - $usedQuota);
    }
}
