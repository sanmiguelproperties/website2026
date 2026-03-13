<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorporateEmailMessage extends Model
{
    protected $fillable = [
        'corporate_email_account_id',
        'user_id',
        'direction',
        'folder',
        'external_uid',
        'external_message_id',
        'subject',
        'from_email',
        'from_name',
        'to_recipients',
        'cc_recipients',
        'bcc_recipients',
        'body_text',
        'body_html',
        'raw_headers',
        'status',
        'received_at',
        'sent_at',
        'read_at',
        'meta',
    ];

    protected $casts = [
        'to_recipients' => 'array',
        'cc_recipients' => 'array',
        'bcc_recipients' => 'array',
        'meta' => 'array',
        'received_at' => 'datetime',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(CorporateEmailAccount::class, 'corporate_email_account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if ($this->read_at !== null) {
            return;
        }

        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }
}
