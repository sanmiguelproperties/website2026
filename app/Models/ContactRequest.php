<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactRequest extends Model
{
    use SoftDeletes;

    public const SOURCE_PROPERTY_FORM = 'property_form';
    public const SOURCE_PROPERTY_DETAIL_FORM = 'property_detail_form';
    public const SOURCE_SELLER_FORM = 'seller_form';
    public const SOURCE_CONTACT_PAGE_FORM = 'contact_page_form';
    public const SOURCE_HOME_CONTACT_FORM = 'home_contact_form';
    public const SOURCE_FOOTER_NEWSLETTER = 'footer_newsletter';
    public const SOURCE_WEBSITE_CONTACT_FORM = 'website_contact_form';
    public const SOURCE_MANUAL_ENTRY = 'manual_entry';
    public const SOURCE_REFERRAL = 'referral';
    public const SOURCE_FACEBOOK = 'facebook';
    public const SOURCE_TIKTOK = 'tiktok';
    public const SOURCE_INSTAGRAM = 'instagram';
    public const SOURCE_WHATSAPP = 'whatsapp';
    public const SOURCE_PHONE_CALL = 'phone_call';
    public const SOURCE_EMAIL = 'email';
    public const SOURCE_GOOGLE_ADS = 'google_ads';
    public const SOURCE_REAL_ESTATE_PORTAL = 'real_estate_portal';
    public const SOURCE_EVENT = 'event';
    public const SOURCE_WALK_IN = 'walk_in';
    public const SOURCE_OTHER = 'other';

    public const LEAD_TYPE_BUYER = 'buyer';
    public const LEAD_TYPE_SELLER = 'seller';
    public const LEAD_TYPE_RENTER = 'renter';
    public const LEAD_TYPE_INVESTOR = 'investor';
    public const LEAD_TYPE_GENERAL = 'general';
    public const LEAD_TYPE_NEWSLETTER = 'newsletter';

    public const CONTACT_TYPE_BUYER = 'buyer';
    public const CONTACT_TYPE_SELLER = 'seller';
    public const CONTACT_TYPE_BUYER_SELLER = 'buyer_seller';

    public const PROPERTY_CONTEXT_EXISTING_LISTING = 'existing_listing';
    public const PROPERTY_CONTEXT_SELLER_PROPERTY = 'seller_property';
    public const PROPERTY_CONTEXT_NONE = 'none';

    protected $fillable = [
        'agency_id',
        'property_id',
        'owner_id',
        'mls_agent_id',
        'converted_client_id',
        'property_public_id',
        'property_address',
        'remote_id',
        'source',
        'source_url',
        'referrer_url',
        'lead_type',
        'contact_type',
        'property_context',
        'interest',
        'name',
        'email',
        'phone',
        'locale',
        'message',
        'happened_at',
        'privacy_accepted_at',
        'status',
        'assignment_status',
        'assigned_at',
        'converted_at',
        'sent_to_easybroker_at',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'raw_payload',
    ];

    protected $casts = [
        'happened_at' => 'datetime',
        'privacy_accepted_at' => 'datetime',
        'assigned_at' => 'datetime',
        'converted_at' => 'datetime',
        'sent_to_easybroker_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function assignedMlsAgent(): BelongsTo
    {
        return $this->belongsTo(MLSAgent::class, 'mls_agent_id');
    }

    public function convertedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ContactNote::class);
    }

    public function scopeFromPropertyForms($query)
    {
        return $query->where('source', self::SOURCE_PROPERTY_FORM);
    }

    public function scopeFromPublicForms($query)
    {
        return $query->whereIn('source', self::publicSources());
    }

    public static function publicSources(): array
    {
        return [
            self::SOURCE_PROPERTY_FORM,
            self::SOURCE_PROPERTY_DETAIL_FORM,
            self::SOURCE_SELLER_FORM,
            self::SOURCE_CONTACT_PAGE_FORM,
            self::SOURCE_HOME_CONTACT_FORM,
            self::SOURCE_FOOTER_NEWSLETTER,
            self::SOURCE_WEBSITE_CONTACT_FORM,
            ...array_keys(self::manualSourceLabels()),
        ];
    }

    public static function manualSourceLabels(): array
    {
        return [
            self::SOURCE_MANUAL_ENTRY => 'Carga manual',
            self::SOURCE_REFERRAL => 'Referencia',
            self::SOURCE_FACEBOOK => 'Facebook',
            self::SOURCE_TIKTOK => 'TikTok',
            self::SOURCE_INSTAGRAM => 'Instagram',
            self::SOURCE_WHATSAPP => 'WhatsApp',
            self::SOURCE_PHONE_CALL => 'Llamada telefonica',
            self::SOURCE_EMAIL => 'Correo electronico',
            self::SOURCE_GOOGLE_ADS => 'Google Ads',
            self::SOURCE_REAL_ESTATE_PORTAL => 'Portal inmobiliario',
            self::SOURCE_EVENT => 'Evento o feria',
            self::SOURCE_WALK_IN => 'Visita a oficina',
            self::SOURCE_OTHER => 'Otra fuente',
        ];
    }

    public static function sourceLabels(): array
    {
        return [
            self::SOURCE_PROPERTY_FORM => 'Formulario de propiedad',
            self::SOURCE_PROPERTY_DETAIL_FORM => 'Detalle de propiedad',
            self::SOURCE_SELLER_FORM => 'Vende con nosotros',
            self::SOURCE_CONTACT_PAGE_FORM => 'Pagina de contacto',
            self::SOURCE_HOME_CONTACT_FORM => 'Formulario del home',
            self::SOURCE_FOOTER_NEWSLETTER => 'Newsletter',
            self::SOURCE_WEBSITE_CONTACT_FORM => 'Contacto web',
        ] + self::manualSourceLabels();
    }

    public static function leadTypeLabels(): array
    {
        return [
            self::LEAD_TYPE_BUYER => 'Comprador',
            self::LEAD_TYPE_SELLER => 'Vendedor',
            self::LEAD_TYPE_RENTER => 'Arrendatario',
            self::LEAD_TYPE_INVESTOR => 'Inversionista',
            self::LEAD_TYPE_GENERAL => 'General',
            self::LEAD_TYPE_NEWSLETTER => 'Newsletter',
        ];
    }

    public static function contactTypeLabels(): array
    {
        return [
            self::CONTACT_TYPE_BUYER => 'Comprador',
            self::CONTACT_TYPE_SELLER => 'Vendedor',
            self::CONTACT_TYPE_BUYER_SELLER => 'Comprador y vendedor',
        ];
    }

    public static function propertyContextLabels(): array
    {
        return [
            self::PROPERTY_CONTEXT_EXISTING_LISTING => 'Propiedad publicada',
            self::PROPERTY_CONTEXT_SELLER_PROPERTY => 'Propiedad del vendedor',
            self::PROPERTY_CONTEXT_NONE => 'Sin propiedad',
        ];
    }

    public function isPublicFormLead(): bool
    {
        return in_array($this->source, self::publicSources(), true);
    }

    public function getSourceLabelAttribute(): string
    {
        return self::sourceLabels()[$this->source] ?? ($this->source ? ucfirst(str_replace('_', ' ', $this->source)) : 'Sin origen');
    }

    public function getLeadTypeLabelAttribute(): string
    {
        return self::leadTypeLabels()[$this->lead_type] ?? ($this->lead_type ? ucfirst(str_replace('_', ' ', $this->lead_type)) : 'Sin tipo');
    }

    public function getContactTypeLabelAttribute(): string
    {
        return self::contactTypeLabels()[$this->contact_type] ?? ($this->contact_type ? ucfirst(str_replace('_', ' ', $this->contact_type)) : 'Sin tipo');
    }

    public function getPropertyContextLabelAttribute(): string
    {
        return self::propertyContextLabels()[$this->property_context] ?? ($this->property_context ? ucfirst(str_replace('_', ' ', $this->property_context)) : 'Sin contexto');
    }

    public function getPropertyFormNameAttribute(): ?string
    {
        return $this->property?->title
            ?: data_get($this->raw_payload, 'property_name')
            ?: $this->property_address
            ?: data_get($this->raw_payload, 'property_address')
            ?: ($this->source === self::SOURCE_SELLER_FORM ? 'Solicitud de vendedor' : null);
    }
}

