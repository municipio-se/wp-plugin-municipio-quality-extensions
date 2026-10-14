<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Performance;

use MunicipioQualityExtensions\Performance\MaterialSymbolsMapStyles;
use MunicipioQualityExtensions\Performance\MaterialSymbolsStoreInterface;
use MunicipioQualityExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class MaterialSymbolsMapStylesTest extends TestCase
{
    public function testUsesUniqueLayerIconsAndDefaultWithFilledSiteVariant(): void
    {
        WordPressState::reset();
        WordPressState::$themeMods = ['icon_style' => 'sharp', 'icon_weight' => '600'];
        $store = $this->createMock(MaterialSymbolsStoreInterface::class);
        $names = [];
        $store
            ->expects(static::exactly(3))
            ->method('getIcon')
            ->willReturnCallback(static function (string $name, string $style, int $weight, int $fill) use (
                &$names,
            ): ?string {
                self::assertSame(['sharp', 600, 1], [$style, $weight, $fill]);
                $names[] = $name;
                return $name === 'missing' ? null : '<svg><path d="M0 0"/></svg>';
            });
        $css = (new MaterialSymbolsMapStyles($store))->render('map-test', json_encode([
            'layerGroups' => [['icon' => 'park'], ['icon' => 'park'], ['icon' => 'missing'], ['icon' => '"></style>']],
        ], JSON_THROW_ON_ERROR));
        static::assertSame(['location_on', 'park', 'missing'], $names);
        static::assertStringContainsString(
            '[data-js-interactive-map="map-test"] .interactive-map__marker > [data-material-symbol="park"]::after',
            $css,
        );
        static::assertStringNotContainsString('data-material-symbol="missing"', $css);
        static::assertStringNotContainsString('<svg', $css);
        static::assertStringNotContainsString('</style>', $css);
        static::assertStringStartsWith('@supports ', $css);
    }

    public function testInvalidMapScopeAndDataDoNotGenerateCss(): void
    {
        $store = $this->createMock(MaterialSymbolsStoreInterface::class);
        $store->expects(static::never())->method('getIcon');
        $renderer = new MaterialSymbolsMapStyles($store);
        static::assertSame('', $renderer->render('bad"id', '{}'));
        static::assertSame('', $renderer->render('map-test', 'invalid'));
    }

    public function testMissingStoreKeepsFontMarkers(): void
    {
        $store = $this->createMock(MaterialSymbolsStoreInterface::class);
        $store->method('getIcon')->willReturn(null);
        static::assertSame('', (new MaterialSymbolsMapStyles($store))->render('map-test', '{}'));
    }
}
