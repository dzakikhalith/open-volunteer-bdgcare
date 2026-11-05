<?php

namespace App\Models;

use Database\Factories\RegistrationStatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationStatusHistory extends Model
{
    /** @use HasFactory<RegistrationStatusHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'changed_by',
        'old_status',
        'new_status',
        'notes',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
