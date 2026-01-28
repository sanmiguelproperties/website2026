<?php

namespace App\Jobs;

use App\Models\MediaAsset;
use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadPropertyImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * El número de intentos del job.
     */
    public int $tries = 3;

    /**
     * El número de segundos para esperar antes de reintentar.
     */
    public int $backoff = 60;

    /**
     * La propiedad a la que pertenece la imagen.
     */
    protected int $propertyId;

    /**
     * Los datos del media asset.
     */
    protected array $mediaData;

    /**
     * Create a new job instance.
     */
    public function __construct(int $propertyId, array $mediaData)
    {
        $this->propertyId = $propertyId;
        $this->mediaData = $mediaData;
        $this->onQueue('mls-images');
    }

    /**
     * Get the unique ID for this job (evita descargas duplicadas).
     */
    public function uniqueId(): string
    {
        $url = $this->mediaData['url'] ?? '';
        return 'download-image-' . md5($url);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $url = $this->mediaData['url'] ?? null;
        $position = $this->mediaData['position'] ?? 0;

        if (!$url) {
            Log::error('[DownloadImageJob] ERROR CRÍTICO: No se proporcionó URL para la imagen', [
                'property_id' => $this->propertyId,
                'media_data' => $this->mediaData,
            ]);
            return;
        }

        // Obtener la propiedad para logging
        $property = Property::find($this->propertyId);
        $mlsId = $property?->mls_public_id ?? 'UNKNOWN';

        Log::info('========================================');
        Log::info('[DownloadImageJob] INICIANDO descarga de imagen', [
            'property_id' => $this->propertyId,
            'property_mls_id' => $mlsId,
            'url' => $url,
            'position' => $position,
            'title' => $this->mediaData['title'] ?? null,
            'attempt' => $this->attempts(),
        ]);

        // Obtener la propiedad
        if (!$property) {
            Log::error('[DownloadImageJob] ERROR: Propiedad no encontrada', [
                'property_id' => $this->propertyId,
                'url' => $url,
            ]);
            return;
        }

        Log::info('[DownloadImageJob] Propiedad encontrada', [
            'property_id' => $property->id,
            'mls_public_id' => $property->mls_public_id,
        ]);

        try {
            // Verificar si el MediaAsset ya existe
            $mediaAsset = MediaAsset::where('url', $url)->first();

            Log::info('[DownloadImageJob] Verificando MediaAsset existente', [
                'url' => $url,
                'media_asset_exists' => $mediaAsset !== null,
                'media_asset_id' => $mediaAsset?->id,
                'storage_path' => $mediaAsset?->storage_path,
            ]);

            if ($mediaAsset && $mediaAsset->storage_path && Storage::disk('public')->exists($mediaAsset->storage_path)) {
                // La imagen ya fue descargada, solo vincular a la propiedad
                Log::info('[DownloadImageJob] IMAGEN YA EXISTE LOCALMENTE, vinculando directamente', [
                    'property_id' => $this->propertyId,
                    'property_mls_id' => $mlsId,
                    'media_asset_id' => $mediaAsset->id,
                    'storage_path' => $mediaAsset->storage_path,
                    'size_bytes' => $mediaAsset->size_bytes,
                ]);
                $this->linkMediaToProperty($property, $mediaAsset);
                Log::info('[DownloadImageJob] ✓ FINALIZADO - Imagen ya existía y fue vinculada', [
                    'property_mls_id' => $mlsId,
                    'url' => substr($url, 0, 60) . '...',
                ]);
                return;
            }

            // Verificar si la imagen remota existe antes de descargar
            Log::info('[DownloadImageJob] Verificando URL remota...', [
                'url' => substr($url, 0, 80) . '...',
            ]);
            
            try {
                $headCheck = Http::timeout(10)->head($url);
            } catch (\Exception $e) {
                Log::error('[DownloadImageJob] ERROR: Fallo al verificar URL remota', [
                    'property_mls_id' => $mlsId,
                    'url' => substr($url, 0, 80) . '...',
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
            
            if (!$headCheck->successful()) {
                Log::error('[DownloadImageJob] ERROR: La URL de la imagen no responde (HTTP ' . $headCheck->status() . ')', [
                    'property_id' => $this->propertyId,
                    'property_mls_id' => $mlsId,
                    'url' => substr($url, 0, 80) . '...',
                    'status' => $headCheck->status(),
                ]);
                return;
            }

            Log::info('[DownloadImageJob] URL remota responde OK, iniciando descarga...', [
                'content_type' => $headCheck->header('Content-Type'),
                'content_length' => $headCheck->header('Content-Length'),
            ]);

            // Descargar la imagen
            try {
                $response = Http::timeout(60)->get($url);
            } catch (\Exception $e) {
                Log::error('[DownloadImageJob] ERROR: Fallo al descargar imagen (conexión)', [
                    'property_mls_id' => $mlsId,
                    'url' => substr($url, 0, 80) . '...',
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }

            if (!$response->successful()) {
                Log::error('[DownloadImageJob] ERROR: La descarga falló (HTTP ' . $response->status() . ')', [
                    'property_id' => $this->propertyId,
                    'property_mls_id' => $mlsId,
                    'url' => substr($url, 0, 80) . '...',
                    'status' => $response->status(),
                ]);
                return;
            }

            $content = $response->body();
            $mimeType = $response->header('Content-Type', 'image/jpeg');
            $sizeBytes = strlen($content);
            $checksum = md5($content);

            Log::info('[DownloadImageJob] Imagen descargada del servidor remoto', [
                'property_mls_id' => $mlsId,
                'url' => substr($url, 0, 60) . '...',
                'mime_type' => $mimeType,
                'size_bytes' => $sizeBytes,
                'checksum' => $checksum,
            ]);

            // Validar que el contenido no esté vacío
            if (empty($content)) {
                Log::error('[DownloadImageJob] ERROR: La imagen descargada está VACÍA', [
                    'property_id' => $this->propertyId,
                    'property_mls_id' => $mlsId,
                    'url' => substr($url, 0, 80) . '...',
                ]);
                return;
            }

            // Generar nombre de archivo único
            $extension = $this->getExtensionFromMimeType($mimeType);
            $folderName = $property->mls_folder_name ?? $property->mls_public_id ?? $property->id;
            $fileName = $checksum . '.' . $extension;
            $storagePath = 'mls/' . $folderName . '/' . $fileName;

            Log::info('[DownloadImageJob] Preparando para guardar imagen', [
                'storage_path' => $storagePath,
                'folder_name' => $folderName,
                'file_name' => $fileName,
            ]);

            // Crear la carpeta si no existe
            $directory = dirname(storage_path('app/public/' . $storagePath));
            if (!is_dir($directory)) {
                Log::info('[DownloadImageJob] Creando directorio', ['directory' => $directory]);
                if (!mkdir($directory, 0755, true)) {
                    Log::error('[DownloadImageJob] ERROR: No se pudo crear el directorio', [
                        'directory' => $directory,
                    ]);
                    return;
                }
            }

            // Guardar en storage/app/public/mls/...
            $saved = Storage::disk('public')->put($storagePath, $content);

            if (!$saved) {
                Log::error('[DownloadImageJob] ERROR: Fallo al guardar en storage', [
                    'property_id' => $this->propertyId,
                    'storage_path' => $storagePath,
                ]);
                return;
            }

            // Verificar que se guardó correctamente
            if (!Storage::disk('public')->exists($storagePath)) {
                Log::error('[DownloadImageJob] ERROR: La imagen no existe después de guardar', [
                    'property_id' => $this->propertyId,
                    'storage_path' => $storagePath,
                ]);
                return;
            }

            Log::info('[DownloadImageJob] ✓ Imagen guardada exitosamente en storage', [
                'storage_path' => $storagePath,
                'exists' => Storage::disk('public')->exists($storagePath),
                'size' => Storage::disk('public')->size($storagePath),
            ]);

            // Crear o actualizar el MediaAsset
            if (!$mediaAsset) {
                $mediaAsset = MediaAsset::create([
                    'type' => 'image',
                    'provider' => 'mls',
                    'url' => $url,
                    'storage_path' => $storagePath,
                    'mime_type' => $mimeType,
                    'size_bytes' => $sizeBytes,
                    'checksum' => $checksum,
                    'name' => $this->mediaData['title'] ?? basename(parse_url($url, PHP_URL_PATH)),
                    'alt' => $this->mediaData['alt'] ?? $this->mediaData['title'] ?? null,
                    'downloaded_at' => now(),
                ]);
                
                Log::info('[DownloadImageJob] ✓ MediaAsset CREADO', [
                    'media_asset_id' => $mediaAsset->id,
                    'url' => substr($url, 0, 60) . '...',
                    'storage_path' => $storagePath,
                ]);
            } else {
                // Actualizar información de descarga
                $mediaAsset->update([
                    'storage_path' => $storagePath,
                    'mime_type' => $mimeType,
                    'size_bytes' => $sizeBytes,
                    'checksum' => $checksum,
                    'downloaded_at' => now(),
                ]);
                
                Log::info('[DownloadImageJob] ✓ MediaAsset ACTUALIZADO', [
                    'media_asset_id' => $mediaAsset->id,
                    'url' => substr($url, 0, 60) . '...',
                    'storage_path' => $storagePath,
                ]);
            }

            // Vincular a la propiedad
            $this->linkMediaToProperty($property, $mediaAsset);

            Log::info('========================================');
            Log::info('[DownloadImageJob] ✓ PROCESO COMPLETADO EXITOSAMENTE', [
                'property_id' => $this->propertyId,
                'property_mls_id' => $mlsId,
                'media_asset_id' => $mediaAsset->id,
                'storage_path' => $storagePath,
                'size_bytes' => $sizeBytes,
                'position' => $position,
                'url' => substr($url, 0, 60) . '...',
            ]);
            Log::info('========================================');

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('[DownloadImageJob] ERROR DE CONEXIÓN HTTP', [
                'property_id' => $this->propertyId,
                'property_mls_id' => $mlsId,
                'url' => substr($url, 0, 80) . '...',
                'error' => $e->getMessage(),
                'response' => $e->response?->body(),
            ]);
            throw $e; // Re-lanzar para retry en errores de red
        } catch (\Throwable $e) {
            Log::error('[DownloadImageJob] ERROR GENERAL', [
                'property_id' => $this->propertyId,
                'property_mls_id' => $mlsId,
                'url' => substr($url, 0, 80) . '...',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e; // Re-lanzar para retry
        }
    }

    /**
     * Vincula el media asset a la propiedad.
     * Si ya existe la relación, actualiza los datos del pivot.
     */
    protected function linkMediaToProperty(Property $property, MediaAsset $mediaAsset): void
    {
        $position = $this->mediaData['position'] ?? 0;
        $title = $this->mediaData['title'] ?? null;
        $sourceUrl = $this->mediaData['url'] ?? null;

        Log::debug('[DownloadImageJob] Vinculando imagen a propiedad', [
            'property_id' => $property->id,
            'property_mls_id' => $property->mls_public_id,
            'media_asset_id' => $mediaAsset->id,
            'position' => $position,
        ]);

        // Verificar si ya está vinculado
        $existingPivot = $property->mediaAssets()
            ->where('media_asset_id', $mediaAsset->id)
            ->first();

        if ($existingPivot) {
            // Actualizar datos del pivot si es necesario
            $existingPivot->pivot->update([
                'title' => $title,
                'position' => $position,
                'checksum' => $mediaAsset->checksum,
                'source_url' => $sourceUrl,
                'raw_payload' => json_encode($this->mediaData),
            ]);

            Log::info('[DownloadImageJob] ✓ Pivot ACTUALIZADO', [
                'property_mls_id' => $property->mls_public_id,
                'media_asset_id' => $mediaAsset->id,
                'position' => $position,
            ]);
        } else {
            // Primera imagen como portada
            if ($position === 0) {
                $property->update(['cover_media_asset_id' => $mediaAsset->id]);
                Log::info('[DownloadImageJob] ✓ Primera imagen establecida como COVER', [
                    'property_mls_id' => $property->mls_public_id,
                    'media_asset_id' => $mediaAsset->id,
                ]);
            }

            // Vincular
            $property->mediaAssets()->attach($mediaAsset->id, [
                'role' => 'image',
                'title' => $title,
                'position' => $position,
                'checksum' => $mediaAsset->checksum,
                'source_url' => $sourceUrl,
                'raw_payload' => json_encode($this->mediaData),
            ]);

            Log::info('[DownloadImageJob] ✓ Nueva relación CREADA (attach)', [
                'property_mls_id' => $property->mls_public_id,
                'media_asset_id' => $mediaAsset->id,
                'position' => $position,
                'role' => 'image',
            ]);
        }

        // Verificar estado final
        $finalMediaCount = $property->mediaAssets()->count();
        $finalImagesCount = $property->mediaAssets()->wherePivot('role', 'image')->count();
        
        Log::debug('[DownloadImageJob] Vinculación completada', [
            'property_mls_id' => $property->mls_public_id,
            'media_asset_id' => $mediaAsset->id,
            'total_media' => $finalMediaCount,
            'total_images' => $finalImagesCount,
        ]);
    }

    /**
     * Obtiene la extensión del archivo desde el MIME type.
     */
    protected function getExtensionFromMimeType(string $mimeType): string
    {
        return match ($mimeType) {
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'image/jpeg', 'image/jpg' => 'jpg',
            default => 'jpg',
        };
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('========================================');
        Log::error('[DownloadImageJob] ❌ Job FALLÓ después de todos los reintentos', [
            'property_id' => $this->propertyId,
            'url' => $this->mediaData['url'] ?? null,
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
        Log::error('========================================');
    }
}
