<?php

namespace Tests\Unit;

use App\Support\SocialLinks;
use PHPUnit\Framework\TestCase;

class SocialLinksTest extends TestCase
{
    public function test_social_settings_are_normalized_and_sorted(): void
    {
        $links = SocialLinks::fromSettings([
            'site_name' => 'San Miguel Properties',
            'social_tiktok' => 'https://tiktok.com/@sanmiguelproperties',
            'social_youtube' => 'https://youtube.com/@sanmiguelproperties',
            'social_facebook' => 'https://facebook.com/sanmiguelproperties',
            'social_twitter' => 'https://x.com/sanmiguelprops',
            'social_instagram' => '',
        ]);

        $this->assertSame(
            ['facebook', 'x', 'youtube', 'tiktok'],
            array_column($links, 'network')
        );
        $this->assertSame('X', $links[1]['label']);
    }

    public function test_unknown_social_networks_keep_a_readable_label(): void
    {
        $links = SocialLinks::fromSettings([
            'social_custom_network' => 'https://example.com/sanmiguel',
        ]);

        $this->assertSame('custom_network', $links[0]['network']);
        $this->assertSame('Custom Network', $links[0]['label']);
    }
}
