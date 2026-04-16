<?php

namespace Tests\Unit;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class BladeMojibakeTest extends TestCase
{
    public function test_blade_views_do_not_contain_mojibake_sequences(): void
    {
        $viewsPath = resource_path('views');
        $pattern = '/(?:\x{00C3}[\x{0080}-\x{00BF}]|\x{00C2}[\x{0080}-\x{00BF}]|\x{00E2}\x{20AC}[\x{0098}-\x{00BF}]|\x{FFFD})/u';
        $issues = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($viewsPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $path = $file->getPathname();
            if (!str_ends_with($path, '.blade.php')) {
                continue;
            }

            $content = file_get_contents($path);
            if ($content === false) {
                $issues[] = "{$path}: unreadable file";
                continue;
            }

            if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE) !== 1) {
                continue;
            }

            $firstMatch = $matches[0][0];
            $firstOffset = $matches[0][1];
            $line = substr_count(substr($content, 0, $firstOffset), "\n") + 1;
            $excerpt = trim(preg_replace('/\s+/', ' ', $firstMatch) ?? $firstMatch);

            $issues[] = "{$path}:{$line} contains possible mojibake fragment `{$excerpt}`";
        }

        $this->assertSame(
            [],
            $issues,
            "Potential mojibake found in Blade views:\n" . implode("\n", $issues)
        );
    }
}
