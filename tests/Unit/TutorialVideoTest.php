<?php

namespace Tests\Unit;

use App\Models\TutorialVideo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TutorialVideoTest extends TestCase
{
    #[DataProvider('youtubeUrls')]
    public function test_it_extracts_youtube_video_ids(string $url, ?string $expected): void
    {
        $this->assertSame($expected, TutorialVideo::extractYoutubeVideoId($url));
    }

    public static function youtubeUrls(): array
    {
        return [
            'watch url' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'short url' => ['https://youtu.be/dQw4w9WgXcQ?si=abc', 'dQw4w9WgXcQ'],
            'embed url' => ['https://www.youtube.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'shorts url' => ['https://youtube.com/shorts/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'invalid host' => ['https://example.com/watch?v=dQw4w9WgXcQ', null],
            'invalid id' => ['https://www.youtube.com/watch?v=bad', null],
        ];
    }
}
