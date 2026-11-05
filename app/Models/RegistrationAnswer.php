<?php

namespace App\Models;

use Database\Factories\RegistrationAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationAnswer extends Model
{
    /** @use HasFactory<RegistrationAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'event_question_id',
        'question_label_snapshot',
        'question_key_snapshot',
        'question_type_snapshot',
        'answer_text',
        'answer_json',
    ];

    protected function casts(): array
    {
        return [
            'answer_json' => 'array',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function eventQuestion(): BelongsTo
    {
        return $this->belongsTo(EventQuestion::class);
    }
}
