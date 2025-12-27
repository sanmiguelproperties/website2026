<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Respuesta de éxito estándar.
     */
    protected function apiSuccess(string $message = 'Operación exitosa', ?string $code = null, $data = null, int $status = 200, array $extra = []): JsonResponse
    {
        $payload = array_merge([
            'success' => true,
            'message' => $message,
            'code'    => $code,
            'data'    => $data,
            'errors'  => null,
        ], $extra);

        return response()->json($payload, $status);
    }

    /**
     * Respuesta 201 Created estándar.
     */
    protected function apiCreated(string $message = 'Creado', ?string $code = null, $data = null, array $extra = []): JsonResponse
    {
        return $this->apiSuccess($message, $code, $data, 201, $extra);
    }

    /**
     * Respuesta de error estándar.
     *
     * Firma: apiError($message, $code = 'SERVER_ERROR', ?array $errors = null, $data = null, int $status = 500, array $extra = [])
     */
    protected function apiError(string $message = 'Error', string $code = 'SERVER_ERROR', ?array $errors = null, $data = null, int $status = 500, array $extra = []): JsonResponse
    {
        $payload = array_merge([
            'success' => false,
            'message' => $message,
            'code'    => $code,
            'data'    => $data,
            'errors'  => $errors,
        ], $extra);

        return response()->json($payload, $status);
    }

    /**
     * 422 Unprocessable Entity (errores de validación).
     */
    protected function apiValidationError(array $errors, string $message = 'Datos de entrada inválidos', string $code = 'VALIDATION_ERROR'): JsonResponse
    {
        return $this->apiError($message, $code, $errors, null, 422);
    }

    /**
     * 403 Forbidden.
     */
    protected function apiForbidden(string $message = 'Permisos insuficientes', string $code = 'FORBIDDEN'): JsonResponse
    {
        return $this->apiError($message, $code, null, null, 403);
    }

    /**
     * 404 Not Found.
     */
    protected function apiNotFound(string $message = 'Recurso no encontrado', string $code = 'NOT_FOUND'): JsonResponse
    {
        return $this->apiError($message, $code, null, null, 404);
    }

    /**
     * 500 Server Error con mensaje acorde a APP_DEBUG.
     */
    protected function apiServerError(\Throwable $e, string $code = 'SERVER_ERROR'): JsonResponse
    {
        $message = config('app.debug') ? $e->getMessage() : 'Error inesperado del servidor';
        return $this->apiError($message, $code, null, null, 500);
    }

    /**
     * Respuesta específica para errores de autenticación.
     */
    protected function apiAuthError(string $message = 'Token inválido o expirado', string $code = 'AUTH_ERROR'): JsonResponse
    {
        return $this->apiError($message, $code, null, null, 401);
    }

    /**
     * Respuesta específica para errores de permisos.
     */
    protected function apiForbiddenError(string $message = 'No tienes permisos para realizar esta acción', string $code = 'FORBIDDEN_ERROR'): JsonResponse
    {
        return $this->apiError($message, $code, null, null, 403);
    }

    /**
     * Adaptador: éxito en el formato que espera el frontend.
     * jsonSuccess($data, $message, $status, $extra)
     */
    protected function jsonSuccess($data = null, string $message = 'Operación exitosa', int $status = 200, array $extra = []): JsonResponse
    {
        // apiSuccess($message, $code = null, $data = null, $status = 200, $extra = [])
        return $this->apiSuccess($message, null, $data, $status, $extra);
    }

    /**
     * Adaptador: error en el formato que espera el frontend.
     * jsonError($message, $status, $errors, $code, $data, $extra)
     */
    protected function jsonError(string $message = 'Error', int $status = 400, $errors = null, string $code = 'BAD_REQUEST', $data = null, array $extra = []): JsonResponse
    {
        // Orden correcto hacia apiError: (message, code, errors, data, status, extra)
        return $this->apiError($message, $code, $errors, $data, $status, $extra);
    }

    /**
     * Adaptador: 404 en el formato que espera el frontend.
     */
    protected function jsonNotFound(string $message = 'Recurso no encontrado'): JsonResponse
    {
        return $this->apiNotFound($message);
    }
}
