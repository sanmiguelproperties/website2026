<?php

namespace Tests\Unit;

use App\Models\FrontendColorSetting;
use Tests\TestCase;

class SellWithUsColorSettingsTest extends TestCase
{
    public function test_sell_with_us_colors_are_grouped_by_section(): void
    {
        $view = FrontendColorSetting::getAvailableViews()['sell-with-us'] ?? null;

        $this->assertNotNull($view);
        $this->assertSame(
            ['sell_hero', 'sell_form', 'sell_content', 'sell_guide', 'sell_testimonials'],
            $view['groups']
        );
    }

    public function test_sell_with_us_defaults_include_section_backgrounds_and_legacy_group(): void
    {
        $defaults = FrontendColorSetting::getDefaultColorsForView('sell-with-us');
        $allDefaults = FrontendColorSetting::getDefaultColors();

        $this->assertSame('#0f172a', $defaults['sell_hero']['section_bg']);
        $this->assertSame('#ffffff', $defaults['sell_form']['card_bg']);
        $this->assertSame('#ffffff', $defaults['sell_content']['page_bg']);
        $this->assertSame('#0f172a', $defaults['sell_guide']['section_bg']);
        $this->assertSame('#f8fafc', $defaults['sell_testimonials']['section_bg']);
        $this->assertArrayHasKey('sell_page', $allDefaults);
    }
}
