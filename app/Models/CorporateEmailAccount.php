<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class CorporateEmailAccount extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email_address',
        'from_name',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_validate_cert',
        'imap_username',
        'imap_password',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'is_active',
        'last_sync_at',
        'last_sync_status',
        'last_sync_error',
        'notes',
    ];

    protected $casts = [
        'imap_port' => 'integer',
        'smtp_port' => 'integer',
        'imap_validate_cert' => 'boolean',
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    protected $hidden = [
        'imap_password',
        'smtp_password',
    ];

    protected $appends = [
        'imap_password_masked',
        'smtp_password_masked',
        'has_imap_password',
        'has_smtp_password',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CorporateEmailMessage::class, 'corporate_email_account_id');
    }

    public function setImapPasswordAttribute(?string $value): void
    {
        $this->attributes['imap_password'] = $this->encryptValue($value);
    }

    public function setSmtpPasswordAttribute(?string $value): void
    {
        $this->attributes['smtp_password'] = $this->encryptValue($value);
    }

    public function getImapPasswordDecryptedAttribute(): ?string
    {
        return $this->decryptValue($this->attributes['imap_password'] ?? null);
    }

    public function getSmtpPasswordDecryptedAttribute(): ?string
    {
        return $this->decryptValue($this->attributes['smtp_password'] ?? null);
    }

    public function getImapPasswordMaskedAttribute(): ?string
    {
        return $this->maskSecret($this->imap_password_decrypted);
    }

    public function getSmtpPasswordMaskedAttribute(): ?string
    {
        return $this->maskSecret($this->smtp_password_decrypted);
    }

    public function getHasImapPasswordAttribute(): bool
    {
        return !empty($this->attributes['imap_password']);
    }

    public function getHasSmtpPasswordAttribute(): bool
    {
        return !empty($this->attributes['smtp_password']);
    }

    public function getImapUsernameForAuth(): string
    {
        return $this->imap_username ?: $this->email_address;
    }

    public function getSmtpUsernameForAuth(): string
    {
        return $this->smtp_username ?: $this->email_address;
    }

    public function isConfiguredForSync(): bool
    {
        return $this->is_active
            && !empty($this->imap_host)
            && !empty($this->getImapUsernameForAuth())
            && !empty($this->imap_password_decrypted);
    }

    public function isConfiguredForSend(): bool
    {
        return $this->is_active
            && !empty($this->smtp_host)
            && !empty($this->getSmtpUsernameForAuth())
            && !empty($this->smtp_password_decrypted)
            && !empty($this->email_address);
    }

    protected function encryptValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            // If this works, value is already encrypted.
            Crypt::decryptString($value);
            return $value;
        } catch (\Throwable $e) {
            return Crypt::encryptString($value);
        }
    }

    protected function decryptValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            // Support legacy plain values if any.
            return $value;
        }
    }

    protected function maskSecret(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $length = strlen($value);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 2) . str_repeat('*', $length - 4) . substr($value, -2);
    }
}
