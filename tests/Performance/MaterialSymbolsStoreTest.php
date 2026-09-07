<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Performance;

use MunicipioQualityExtensions\Performance\MaterialSymbolsStore;
use PHPUnit\Framework\TestCase;

final class MaterialSymbolsStoreTest extends TestCase
{
    private string $directory;
    private string $packPath;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/mqe-symbols-' . bin2hex(random_bytes(8));
        $release = $this->directory . '/releases/' . str_repeat('a', 40);
        mkdir($release, 0o755, true);
        symlink('releases/' . str_repeat('a', 40), $this->directory . '/current');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M0 0"/></svg>';
        $this->packPath = $release . '/rounded-wght400-fill0.pack';
        file_put_contents($this->packPath, $svg);
        file_put_contents($release . '/rounded-wght400-fill0.json', json_encode([
            'format' => 1,
            'variant' => 'rounded-wght400-fill0',
            'icons' => [
                'search' => [
                    'offset' => 0,
                    'length' => strlen($svg),
                    'sha256' => hash('sha256', $svg),
                ],
            ],
        ], JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $entry) {
            $entry->isDir() && !$entry->isLink() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }
        rmdir($this->directory);
    }

    public function testItReadsAndVerifiesOneIndexedIcon(): void
    {
        $svg = (new MaterialSymbolsStore($this->directory))->getIcon('search', 'rounded', 400, 0);

        static::assertIsString($svg);
        static::assertStringContainsString('fill="currentColor"', $svg);
    }

    public function testItRejectsTamperedPackData(): void
    {
        file_put_contents($this->packPath, '<svg><path d="tampered"/></svg>');

        static::assertNull((new MaterialSymbolsStore($this->directory))->getIcon('search', 'rounded', 400, 0));
    }

    public function testItRejectsPathsOutsideTheReleaseDirectory(): void
    {
        unlink($this->directory . '/current');
        symlink(sys_get_temp_dir(), $this->directory . '/current');

        static::assertNull((new MaterialSymbolsStore($this->directory))->getIcon('search', 'rounded', 400, 0));
    }
}
