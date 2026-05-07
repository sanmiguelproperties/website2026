<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    public const SOURCE_PROPERTY_FORM = 'property_form';
    public const SOURCE_SELLER_FORM = 'seller_form';
    public const SOURCE_CONTACT_FORM = 'contact_form';
    public const STATUS_ACTIVE = 'active';

    public const CONTACT_TYPE_BUYER = 'buyer';
    public const CONTACT_TYPE_SELLER = 'seller';
    public const CONTACT_TYPE_BUYER_SELLER = 'buyer_seller';

    protected $fillable = [
        'contact_request_id',
        'property_id',
        'owner_id',
        'name',
        'email',
        'phone',
        'source',
        'contact_type',
        'status',
        'notes',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function contactRequest(): BelongsTo
    {
        return $this->belongsTo(ContactRequest::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ClientComment::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(ClientVisit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public static function contactTypeLabels(): array
    {
        return [
            self::CONTACT_TYPE_BUYER => 'Comprador',
            self::CONTACT_TYPE_SELLER => 'Vendedor',
            self::CONTACT_TYPE_BUYER_SELLER => 'Comprador y vendedor',
        ];
    }

    public function getContactTypeLabelAttribute(): string
    {
        return self::contactTypeLabels()[$this->contact_type] ?? ($this->contact_type ? ucfirst(str_replace('_', ' ', $this->contact_type)) : 'Sin tipo');
    }
}
