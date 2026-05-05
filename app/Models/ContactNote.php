<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contact_request_id',
        'user_id',
        'body',
        'note_type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function contactRequest(): BelongsTo
    {
        return $this->belongsTo(ContactRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
