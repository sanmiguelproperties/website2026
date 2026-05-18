<?php

namespace App\Support;

class SocialLinks
{
    private const LABELS = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'x' => 'X',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
        'pinterest' => 'Pinterest',
        'whatsapp' => 'WhatsApp',
        'threads' => 'Threads',
        'telegram' => 'Telegram',
        'vimeo' => 'Vimeo',
        'website' => 'Website',
    ];

    private const ORDER = [
        'facebook',
        'instagram',
        'x',
        'linkedin',
        'youtube',
        'tiktok',
        'pinterest',
        'whatsapp',
        'threads',
        'telegram',
        'vimeo',
        'website',
    ];

    /**
     * @param array<string, mixed> $settings
     * @return array<int, array{key: string, network: string, label: string, url: string}>
     */
    public static function fromSettings(array $settings): array
    {
        $links = [];

        foreach ($settings as $key => $value) {
            $key = (string) $key;
            $url = trim((string) $value);

            if ($url === '' || !str_starts_with($key, 'social_')) {
                continue;
            }

            $network = self::normalizeNetwork(substr($key, 7));
            $links[] = [
                'key' => $key,
                'network' => $network,
                'label' => self::label($network),
                'url' => $url,
                '_order' => self::order($network),
            ];
        }

        usort($links, static function (array $left, array $right): int {
            return [$left['_order'], $left['label']] <=> [$right['_order'], $right['label']];
        });

        return array_map(static function (array $link): array {
            unset($link['_order']);

            return $link;
        }, $links);
    }

    public static function normalizeNetwork(string $network): string
    {
        $network = strtolower(trim(str_replace(['-', ' '], '_', $network)));

        return match ($network) {
            'twitter', 'x_twitter' => 'x',
            'linked_in' => 'linkedin',
            'tik_tok' => 'tiktok',
            default => $network !== '' ? $network : 'website',
        };
    }

    public static function label(string $network): string
    {
        $network = self::normalizeNetwork($network);

        return self::LABELS[$network] ?? ucwords(str_replace('_', ' ', $network));
    }

    private static function order(string $network): int
    {
        $index = array_search($network, self::ORDER, true);

        return $index === false ? 100 : $index;
    }
}
