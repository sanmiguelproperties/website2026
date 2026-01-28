<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Modelo para la configuración del MLS AMPI San Miguel de Allende.
 * 
 * La API Key se almacena encriptada en la base de datos para mayor seguridad.
 */
class MLSConfig extends Model
{
    protected $table = 'mls_configs';

    protected $fillable = [
        'name',
        'api_key',
        'base_url',
        'rate_limit',
        'timeout',
        'batch_size',
        'is_active',
        'sync_mode',
        'last_sync_at',
        'last_sync_created',
        'last_sync_updated',
        'last_sync_unpublished',
        'last_sync_errors',
        'last_sync_total_fetched',
        'last_sync_page',
        'last_sync_cursor',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rate_limit' => 'integer',
        'timeout' => 'integer',
        'batch_size' => 'integer',
        'last_sync_at' => 'datetime',
        'last_sync_created' => 'integer',
        'last_sync_updated' => 'integer',
        'last_sync_unpublished' => 'integer',
        'last_sync_errors' => 'integer',
        'last_sync_total_fetched' => 'integer',
        'last_sync_page' => 'integer',
    ];

    /**
     * Campos ocultos en la serialización por defecto.
     * api_key_decrypted no debe exponerse en respuestas JSON.
     */
    protected $hidden = [
        'api_key',
    ];

    /**
     * Atributos computados que se agregan automáticamente.
     */
    protected $appends = [
        'api_key_masked',
        'has_api_key',
    ];

    /**
     * Encripta la API key antes de guardarla.
     */
    public function setApiKeyAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['api_key'] = null;
            return;
        }

        // Solo encriptar si el valor es diferente (no está ya encriptado)
        // Intenta desencriptar para ver si ya está encriptado
        try {
            Crypt::decryptString($value);
            // Si llegamos aquí, ya está encriptado
            $this->attributes['api_key'] = $value;
        } catch (\Exception $e) {
            // No está encriptado, encriptar ahora
            $this->attributes['api_key'] = Crypt::encryptString($value);
        }
    }

    /**
     * Desencripta la API key al leerla.
     * Usar este accessor solo internamente, no en respuestas API.
     */
    public function getApiKeyDecryptedAttribute(): ?string
    {
        if (empty($this->attributes['api_key'])) {
            return null;
        }

        try {
            return Crypt::decryptString($this->attributes['api_key']);
        } catch (\Exception $e) {
            // Si falla la desencriptación, devolver el valor tal cual
            // (podría ser un valor antiguo no encriptado)
            return $this->attributes['api_key'];
        }
    }

    /**
     * Devuelve la API key ofuscada para mostrar en la UI.
     */
    public function getApiKeyMaskedAttribute(): ?string
    {
        $decrypted = $this->api_key_decrypted;
        
        if (empty($decrypted)) {
            return null;
        }

        $length = strlen($decrypted);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($decrypted, 0, 4) . str_repeat('*', $length - 8) . substr($decrypted, -4);
    }

    /**
     * Indica si tiene una API key configurada.
     */
    public function getHasApiKeyAttribute(): bool
    {
        return !empty($this->attributes['api_key']);
    }

    /**
     * Obtiene la configuración activa (singleton pattern básico).
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)
            ->orderBy('id', 'asc')
            ->first();
    }

    /**
     * Obtiene o crea la configuración principal.
     */
    public static function getOrCreateDefault(): self
    {
        $config = static::getActive();

        if (!$config) {
            $config = static::create([
                'name' => 'Principal',
                'base_url' => 'https://ampisanmigueldeallende.com/api/v1',
                'rate_limit' => 10,
                'timeout' => 30,
                'batch_size' => 50,
                'sync_mode' => 'incremental',
                'is_active' => true,
            ]);
        }

        return $config;
    }

    /**
     * Registra el resultado de una sincronización.
     */
    public function recordSyncResult(array $stats): void
    {
        $this->update([
            'last_sync_at' => now(),
            'last_sync_created' => $stats['created'] ?? 0,
            'last_sync_updated' => $stats['updated'] ?? 0,
            'last_sync_unpublished' => $stats['unpublished'] ?? 0,
            'last_sync_errors' => $stats['errors'] ?? 0,
            'last_sync_total_fetched' => $stats['total_fetched'] ?? 0,
            'last_sync_page' => $stats['last_page'] ?? null,
            'last_sync_cursor' => $stats['last_cursor'] ?? null,
        ]);
    }

    /**
     * Verifica si la configuración está completa para sincronizar.
     */
    public function isConfigured(): bool
    {
        return $this->is_active && !empty($this->api_key_decrypted);
    }

    /**
     * Obtiene los valores permitidos para estados del MLS.
     */
    public static function getAllowedStatuses(): array
    {
        return [
            'For Sale',
            'For Rent',
            'Price Reduction',
            'Contract Pending',
            'Under Contract',
        ];
    }

    /**
     * Obtiene los valores permitidos para categorías del MLS.
     */
    public static function getAllowedCategories(): array
    {
        return [
            'Residential',
            'Land and Lots',
            'Commercial',
            'Pre Sales',
        ];
    }

    /**
     * Obtiene los valores permitidos para monedas del MLS.
     */
    public static function getAllowedCurrencies(): array
    {
        return ['MXN', 'USD', 'CAD', 'EUR'];
    }

    /**
     * Obtiene los valores permitidos para furnished del MLS.
     */
    public static function getAllowedFurnished(): array
    {
        return ['Any', 'yes', 'no', 'partially'];
    }
}
