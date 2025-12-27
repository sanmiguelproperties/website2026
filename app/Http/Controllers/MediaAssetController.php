<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MediaAssetController extends Controller
{
    /**
     * GET /api/media
     */
    public function index(Request $request): JsonResponse
    {
        $query = MediaAsset::query();
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed') || $request->boolean('with_inactive')) {
            $query->withTrashed();
        }

        if ($request->filled('type')) {
            $query->whereIn('type', (array)$request->input('type'));
        }

        if ($request->filled('provider')) {
            $query->whereIn('provider', (array)$request->input('provider'));
        }

        if ($request->filled('search')) {
            $search = trim((string)$request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('alt', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'desc');
        $order = $request->input('order', 'created_at');
        $validOrders = ['created_at', 'updated_at', 'name', 'type'];
        if (!in_array($order, $validOrders, true)) {
            $order = 'created_at';
        }
        $sort = $sort === 'asc' ? 'asc' : 'desc';
        $query->orderBy($order, $sort);

        $perPage = (int)$request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));
        $data = $query->paginate($perPage);

        return $this->apiSuccess('Listado de medios', 'MEDIA_LIST', $data);
    }

    /**
     * POST /api/media
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required_without:url|file|max:204800',
            'url' => 'required_without:file|url',
            'type' => 'nullable|string|in:image,video,audio,document',
            'provider' => 'nullable|string',
            'duration_seconds' => 'nullable|integer|min:0',
            'name' => 'nullable|string|max:255',
            'alt' => 'nullable|string|max:255',
        ], [
            'file.required_without' => 'Debes enviar un archivo o una url.',
            'url.required_without' => 'Debes enviar un archivo o una url.',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $assetData = [
            'type' => null,
            'path' => null,
            'url' => null,
            'provider' => $request->input('provider'),
            'duration_seconds' => $request->input('duration_seconds'),
            'size_bytes' => null,
            'mime_type' => null,
            'name' => $request->input('name'),
            'alt' => $request->input('alt'),
        ];

        // URL externa
        if ($request->filled('url')) {
            $assetData['url'] = (string)$request->input('url');

            $type = $request->input('type');
            if (!$type) {
                $extPath = parse_url($assetData['url'], PHP_URL_PATH) ?? '';
                $ext = strtolower(pathinfo($extPath, PATHINFO_EXTENSION));
                $map = [
                    'jpg' => 'image', 'jpeg' => 'image', 'png' => 'image', 'gif' => 'image', 'webp' => 'image', 'svg' => 'image',
                    'mp4' => 'video', 'mov' => 'video', 'avi' => 'video', 'webm' => 'video', 'mkv' => 'video',
                    'mp3' => 'audio', 'wav' => 'audio', 'ogg' => 'audio', 'opus' => 'audio',
                    'pdf' => 'document', 'txt' => 'document', 'csv' => 'document', 'xlsx' => 'document', 'xls' => 'document',
                    'doc' => 'document', 'docx' => 'document', 'ppt' => 'document', 'pptx' => 'document',
                    'rtf' => 'document', 'zip' => 'document', 'rar' => 'document'
                ];
                $type = $map[$ext] ?? 'document';
            }
            $assetData['type'] = $type;

            $media = MediaAsset::create($assetData);
            return $this->apiCreated('Medio creado exitosamente', 'MEDIA_CREATED', $media);
        }

        // Archivo local
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            if (!$file->isValid()) {
                return $this->apiValidationError(['file' => ['Archivo inválido o no recibido correctamente']]);
            }

            $mimeType = $file->getMimeType() ?? $file->getClientMimeType();
            $mimeType = explode(';', (string)$mimeType)[0];
            $type = $request->type ?? $this->determineTypeFromMime((string)$mimeType);

            $allowedMimes = $this->allowedMimes();
            $allowedExts  = $this->allowedExtensions();
            $ext = strtolower((string)$file->getClientOriginalExtension());

            $mimeOk = isset($allowedMimes[$type]) && in_array($mimeType, $allowedMimes[$type], true);
            $extOk  = isset($allowedExts[$type]) && in_array($ext, $allowedExts[$type], true);

            if (!$mimeOk && !$extOk) {
                return $this->apiValidationError(['file' => ['Tipo de archivo no permitido']]);
            }

            $userId = 1; // Usuario por defecto
            $year   = trim(now()->format('Y'));
            $month  = trim(now()->format('m'));

            $dir = "uploads/{$userId}/{$year}/{$month}";
            $dir = $this->sanitizeDir($dir);

            $disk = 'public';

            $this->ensureWritableDir($disk, $dir);

            try {
                $path = $file->store($dir, $disk);
                if (!$path) {
                    return $this->apiError('No se pudo guardar el archivo', 'SERVER_ERROR', null, null, 500);
                }
            } catch (\Throwable $e) {
                return $this->apiError('No se pudo crear el directorio de destino', 'SERVER_ERROR', null, null, 500);
            }

            $assetData['type']       = $type;
            $assetData['path']       = $path;
            $assetData['url']        = Storage::disk($disk)->url($path);
            $assetData['mime_type']  = $mimeType;
            $assetData['size_bytes'] = $file->getSize();
            if (empty($assetData['name'])) {
                $assetData['name'] = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            }

            if (!$assetData['duration_seconds']) {
                $assetData['duration_seconds'] = $this->getDuration($file, $type);
            }

            $media = MediaAsset::create($assetData);
            return $this->apiCreated('Medio creado exitosamente', 'MEDIA_CREATED', $media);
        }

        return $this->apiValidationError([
            'file' => ['Debes enviar un archivo o una url.'],
            'url'  => ['Debes enviar un archivo o una url.'],
        ], 'Datos de entrada inválidos');
    }

    /**
     * GET /api/media/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = MediaAsset::where('id', $id);
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed') || $request->boolean('with_inactive')) {
            $query->withTrashed();
        }

        $media = $query->first();
        if (!$media) {
            return $this->apiNotFound('Medio no encontrado', 'MEDIA_NOT_FOUND');
        }

        return $this->apiSuccess('Medio obtenido', 'MEDIA_SHOWN', $media);
    }

    /**
     * PATCH /api/media/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $query = MediaAsset::where('id', $id);
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed') || $request->boolean('with_inactive')) {
            $query->withTrashed();
        }

        $media = $query->first();
        if (!$media) {
            return $this->apiNotFound('Medio no encontrado', 'MEDIA_NOT_FOUND');
        }

        $validator = Validator::make($request->all(), [
            'file' => 'nullable|file|max:204800',
            'url' => 'nullable|url',
            'type' => ['nullable', 'string', Rule::in(['image', 'video', 'audio', 'document'])],
            'provider' => 'nullable|string',
            'duration_seconds' => 'nullable|integer|min:0',
            'name' => 'nullable|string|max:255',
            'alt' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $validated = $validator->validated();

        // Reemplazo de archivo
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if (!$file->isValid()) {
                return $this->apiValidationError(['file' => ['Archivo inválido o no recibido correctamente']]);
            }

            $mimeType = $file->getMimeType() ?? $file->getClientMimeType();
            $mimeType = explode(';', (string)$mimeType)[0];
            $type = $validated['type'] ?? $this->determineTypeFromMime((string)$mimeType);

            $allowedMimes = $this->allowedMimes();
            $allowedExts  = $this->allowedExtensions();
            $ext = strtolower((string)$file->getClientOriginalExtension());

            $mimeOk = isset($allowedMimes[$type]) && in_array($mimeType, $allowedMimes[$type], true);
            $extOk  = isset($allowedExts[$type]) && in_array($ext, $allowedExts[$type], true);

            if (!$mimeOk && !$extOk) {
                return $this->apiValidationError(['file' => ['Tipo de archivo no permitido']]);
            }

            if ($media->path) {
                Storage::disk('public')->delete($media->path);
            }

            $userId = 1;
            $year   = trim(now()->format('Y'));
            $month  = trim(now()->format('m'));

            $dir = "uploads/{$userId}/{$year}/{$month}";
            $dir = $this->sanitizeDir($dir);

            $disk = 'public';

            $this->ensureWritableDir($disk, $dir);

            try {
                $path = $file->store($dir, $disk);
                if (!$path) {
                    return $this->apiError('No se pudo guardar el archivo', 'SERVER_ERROR', null, null, 500);
                }
            } catch (\Throwable $e) {
                return $this->apiError('No se pudo crear el directorio de destino', 'SERVER_ERROR', null, null, 500);
            }

            $media->type = $type;
            $media->path = $path;
            $media->url = Storage::disk($disk)->url($path);
            $media->mime_type = $mimeType;
            $media->size_bytes = $file->getSize();
            if (!empty($validated['name'])) {
                $media->name = $validated['name'];
            }
            if (!empty($validated['alt'])) {
                $media->alt = $validated['alt'];
            }
            $media->duration_seconds = $validated['duration_seconds'] ?? $this->getDuration($file, $type);
            $media->provider = $validated['provider'] ?? $media->provider;

            $media->save();

            return $this->apiSuccess('Medio actualizado', 'MEDIA_UPDATED', $media);
        }

        // URL externa
        if ($request->filled('url')) {
            if ($media->path) {
                Storage::disk('public')->delete($media->path);
                $media->path = null;
            }
            $media->url = (string)$request->input('url');
        }

        // Metadatos
        if (!empty($validated['type'])) {
            $media->type = $validated['type'];
        }
        if (!empty($validated['provider'])) {
            $media->provider = $validated['provider'];
        }
        if (!empty($validated['duration_seconds'])) {
            $media->duration_seconds = (int)$validated['duration_seconds'];
        }
        if (!empty($validated['name'])) {
            $media->name = $validated['name'];
        }
        if (!empty($validated['alt'])) {
            $media->alt = $validated['alt'];
        }

        $media->save();
        return $this->apiSuccess('Medio actualizado', 'MEDIA_UPDATED', $media);
    }

    /**
     * DELETE /api/media/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $query = MediaAsset::where('id', $id);

        $media = $query->first();
        if (!$media) {
            return $this->apiNotFound('Medio no encontrado', 'MEDIA_NOT_FOUND');
        }

        // Enviar a papelera (Soft Delete)
        $media->delete();

        return $this->apiSuccess('Medio enviado a la papelera', 'MEDIA_TRASHED', null);
    }

    /**
     * Determina el tipo (image, video, audio, document)
     */
    private function determineTypeFromMime(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mime, 'audio/')) {
            return 'audio';
        }
        return 'document';
    }

    /**
     * MIMEs permitidos por tipo
     */
    private function allowedMimes(): array
    {
        return [
            'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
            'video' => ['video/mp4', 'video/avi', 'video/quicktime', 'video/mov'],
            'audio' => [
                'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav',
                'audio/webm', 'video/webm', 'audio/ogg', 'application/ogg', 'audio/opus',
            ],
            'document' => [
                'application/pdf', 'text/plain',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.ms-powerpoint',
                'text/rtf',
                'text/csv',
                'application/zip',
                'application/x-zip-compressed',
                'application/x-rar-compressed',
            ],
        ];
    }

    /**
     * Extensiones permitidas por tipo (fallback cuando el MIME es genérico).
     */
    private function allowedExtensions(): array
    {
        return [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'video' => ['mp4', 'avi', 'mov', 'quicktime', 'webm', 'mkv'],
            'audio' => ['mp3', 'wav', 'ogg', 'opus'],
            'document' => [
                'pdf', 'txt', 'csv', 'xlsx', 'xls',
                'doc', 'docx', 'ppt', 'pptx', 'rtf', 'zip', 'rar',
            ],
        ];
    }

    /**
     * Calcula duración si quieres con FFmpeg (placeholder)
     */
    private function getDuration($file, $type): ?int
    {
        if (in_array($type, ['video', 'audio'], true)) {
            return null;
        }
        return null;
    }

    /**
     * Sanea path de directorio (sin caracteres raros, sin puntos/espacios al final)
     */
    private function sanitizeDir(string $dir): string
    {
        $dir = str_replace('\\', '/', $dir);
        $dir = preg_replace('~[^\w/\-]~u', '', $dir);
        $dir = preg_replace('~/{2,}~', '/', $dir);
        return rtrim($dir, ". \t\n\r\0\x0B");
    }

    /**
     * Asegura que el directorio exista y sea escribible; da diagnóstico útil en logs.
     */
    private function ensureWritableDir(string $disk, string $dir): array
    {
        $absRoot = Storage::disk($disk)->path('/');
        $absDir  = Storage::disk($disk)->path($dir);

        if (file_exists($absDir) && !is_dir($absDir)) {
            return [
                'ok' => false,
                'reason' => 'file-blocking',
                'message' => 'Existe un archivo con el mismo nombre del directorio',
                'disk' => $disk,
                'dir' => $dir,
                'abs_root' => $absRoot,
                'abs_dir' => $absDir,
            ];
        }

        try {
            if (!Storage::disk($disk)->exists($dir)) {
                Storage::disk($disk)->makeDirectory($dir);
            }
        } catch (\Throwable $e) {
            @mkdir($absDir, 0775, true);
        }

        clearstatcache(true, $absDir);
        $parent = dirname($absDir);

        $result = [
            'ok'         => is_dir($absDir) && is_writable($absDir),
            'disk'       => $disk,
            'dir'        => $dir,
            'abs_root'   => $absRoot,
            'abs_dir'    => $absDir,
            'is_dir'     => is_dir($absDir),
            'exists'     => file_exists($absDir),
            'writable'   => is_writable($absDir),
            'parent'     => $parent,
            'parent_dir' => is_dir($parent),
            'parent_w'   => is_writable($parent),
        ];

        return $result;
    }
}