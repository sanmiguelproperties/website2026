<?php

namespace Tests\Feature;

use App\Models\ManualArticle;
use App\Models\ManualSection;
use App\Models\TutorialVideo;
use App\Support\ManualContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_manual_content_is_created_with_the_schema(): void
    {
        $this->assertSame(10, ManualSection::count());
        $this->assertGreaterThanOrEqual(75, ManualArticle::count());

        $article = ManualArticle::where('slug', 'registrar-propiedad')->firstOrFail();

        $this->assertSame('Registrar una propiedad manualmente', $article->title);
        $this->assertStringContainsString('<h2>Pasos</h2>', $article->content);
        $this->assertSame('properties', $article->related_route_name);
    }

    public function test_reseeding_defaults_does_not_overwrite_edited_articles(): void
    {
        $article = ManualArticle::where('slug', 'consultar-manual')->firstOrFail();
        $article->update(['title' => 'Titulo personalizado']);

        ManualContent::seedDefaults();

        $this->assertSame('Titulo personalizado', $article->fresh()->title);
    }

    public function test_manual_section_can_reference_one_tutorial_video(): void
    {
        $video = TutorialVideo::create([
            'title' => 'Tutorial del capitulo',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
        ]);
        $section = ManualSection::firstOrFail();

        $section->update(['tutorial_video_id' => $video->id]);

        $this->assertTrue($section->fresh()->video->is($video));

        $video->delete();

        $this->assertNull($section->fresh()->tutorial_video_id);
    }
}
