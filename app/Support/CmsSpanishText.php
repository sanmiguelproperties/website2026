<?php

namespace App\Support;

final class CmsSpanishText
{
    public static function repairEncoding(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $value = self::replaceKnownBrokenSequences($value);

        for ($i = 0; $i < 3 && self::looksLikeMojibake($value); $i++) {
            $decoded = self::decodeWindows1252Mojibake($value);

            if ($decoded === null || $decoded === $value) {
                break;
            }

            $decoded = self::replaceKnownBrokenSequences($decoded);

            if (self::mojibakeScore($decoded) > self::mojibakeScore($value)) {
                break;
            }

            $value = $decoded;
        }

        return self::replaceKnownBrokenSequences($value);
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    public static function repairArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = self::repairEncoding($value);
            } elseif (is_array($value)) {
                $data[$key] = self::repairArray($value);
            }
        }

        return $data;
    }

    public static function makeAdminLabel(string $fieldKey, ?string $valueEs = null): string
    {
        $fieldKey = trim($fieldKey);
        $valueEs = trim((string) self::repairEncoding($valueEs ?? ''));

        $directLabels = [
            'page_badge' => 'Badge',
            'page_title' => 'Título',
            'page_title_prefix' => 'Título: prefijo',
            'page_title_highlight' => 'Título: destacado',
            'page_subtitle' => 'Subtítulo',
            'search_label' => 'Etiqueta de búsqueda',
            'search_placeholder' => 'Placeholder de búsqueda',
        ];

        if (isset($directLabels[$fieldKey])) {
            return $directLabels[$fieldKey];
        }

        if (
            in_array($fieldKey, ['cta_back', 'cta_share'], true)
            || (str_starts_with($fieldKey, 'cta_') && preg_match('/(^|_)button(_|$)/', $fieldKey) === 1)
        ) {
            return 'Botón ' . self::lowerFirst($valueEs !== '' ? $valueEs : self::humanizeKey($fieldKey));
        }

        if ($valueEs !== '' && mb_strlen($valueEs) <= 60) {
            return 'Texto: ' . $valueEs;
        }

        return 'Texto: ' . self::humanizeKey($fieldKey);
    }

    private static function looksLikeMojibake(string $value): bool
    {
        return preg_match('/[ÃÂâï]/u', $value) === 1;
    }

    private static function mojibakeScore(string $value): int
    {
        preg_match_all('/[ÃÂâï�]/u', $value, $matches);

        return count($matches[0]);
    }

    private static function decodeWindows1252Mojibake(string $value): ?string
    {
        $decoded = @mb_convert_encoding($value, 'Windows-1252', 'UTF-8');

        if (!is_string($decoded) || !mb_check_encoding($decoded, 'UTF-8')) {
            return null;
        }

        return $decoded;
    }

    private static function replaceKnownBrokenSequences(string $value): string
    {
        return strtr($value, [
            "\u{FFFD}f\u{0081}" => 'Á',
            "\u{FFFD}f\u{00A1}" => 'á',
            "\u{FFFD}f\u{00A9}" => 'é',
            "\u{FFFD}f\u{00AD}" => 'í',
            "\u{FFFD}f\u{00B1}" => 'ñ',
            "\u{FFFD}f\u{00B3}" => 'ó',
            "\u{FFFD}f\u{00BA}" => 'ú',
            "\u{FFFD}f\u{0161}" => 'Ú',
            "\u{FFFD},\u{00A1}" => '¡',
            "\u{FFFD},\u{00BF}" => '¿',
        ]);
    }

    private static function humanizeKey(string $fieldKey): string
    {
        $key = preg_replace('/([a-z])([A-Z])/', '$1_$2', $fieldKey) ?? $fieldKey;
        $key = preg_replace('/^(i18n|cms)_/', '', $key) ?? $key;
        $parts = preg_split('/[_-]+/', mb_strtolower($key)) ?: [];

        $dictionary = [
            'about' => 'nosotros',
            'accept' => 'aceptar',
            'address' => 'dirección',
            'advisor' => 'asesor',
            'agency' => 'agencia',
            'agent' => 'agente',
            'aria' => 'aria',
            'available' => 'disponible',
            'back' => 'volver',
            'badge' => 'badge',
            'bathrooms' => 'baños',
            'bedrooms' => 'recámaras',
            'button' => 'botón',
            'call' => 'llamar',
            'common' => '',
            'company' => 'empresa',
            'construction' => 'construcción',
            'contact' => 'contacto',
            'cta' => 'botón',
            'default' => 'por defecto',
            'desc' => 'descripción',
            'description' => 'descripción',
            'detail' => 'detalle',
            'details' => 'detalles',
            'email' => 'email',
            'error' => 'error',
            'favorite' => 'favorito',
            'features' => 'características',
            'form' => 'formulario',
            'gallery' => 'galería',
            'header' => 'header',
            'home' => 'inicio',
            'label' => '',
            'location' => 'ubicación',
            'map' => 'mapa',
            'message' => 'mensaje',
            'name' => 'nombre',
            'operation' => 'operación',
            'page' => 'página',
            'parking' => 'estacionamientos',
            'phone' => 'teléfono',
            'photo' => 'foto',
            'photos' => 'fotos',
            'placeholder' => 'placeholder',
            'price' => 'precio',
            'privacy' => 'privacidad',
            'properties' => 'propiedades',
            'property' => 'propiedad',
            'required' => 'requerido',
            'retry' => 'reintentar',
            'search' => 'búsqueda',
            'section' => 'sección',
            'seo' => 'SEO',
            'share' => 'compartir',
            'subtitle' => 'subtítulo',
            'success' => 'éxito',
            'tag' => 'etiqueta',
            'text' => 'texto',
            'title' => 'título',
            'updated' => 'actualizado',
            'view' => 'ver',
            'whatsapp' => 'WhatsApp',
        ];

        $translated = array_values(array_filter(array_map(
            static fn (string $part): string => $dictionary[$part] ?? $part,
            $parts
        )));

        $label = trim(implode(' ', $translated));

        return self::upperFirst($label !== '' ? $label : $fieldKey);
    }

    private static function lowerFirst(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        return mb_strtolower(mb_substr($value, 0, 1)) . mb_substr($value, 1);
    }

    private static function upperFirst(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        return mb_strtoupper(mb_substr($value, 0, 1)) . mb_substr($value, 1);
    }
}
