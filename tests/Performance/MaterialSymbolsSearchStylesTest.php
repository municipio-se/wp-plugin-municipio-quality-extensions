<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Performance;

use MunicipioQualityExtensions\Performance\MaterialSymbolsSearchStyles;
use MunicipioQualityExtensions\Performance\MaterialSymbolsStoreInterface;
use MunicipioQualityExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class MaterialSymbolsSearchStylesTest extends TestCase
{
    public function testMissingIconsKeepTheFontFallback(): void
    {
        WordPressState::reset();
        $store = $this->createMock(MaterialSymbolsStoreInterface::class);
        $store->method('getIcon')->willReturn(null);
        static::assertSame('', MaterialSymbolsSearchStyles::render($store));
    }

    public function testAvailableArrowUsesTheSiteVariantAndOnlyMatchesSearchPagination(): void
    {
        WordPressState::reset();
        WordPressState::$themeMods = ['icon_style' => 'sharp', 'icon_weight' => '600'];
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z"/></svg>';
        $store = $this->createMock(MaterialSymbolsStoreInterface::class);
        $store
            ->expects(self::exactly(2))
            ->method('getIcon')
            ->willReturnCallback(static function (string $name, string $style, int $weight, int $fill) use (
                $svg,
            ): ?string {
                self::assertSame(['sharp', 600, 1], [$style, $weight, $fill]);
                return $name === 'keyboard_arrow_right' ? $svg : null;
            });
        $css = MaterialSymbolsSearchStyles::render($store);
        static::assertStringContainsString(
            '[data-js-search-page-pagination] .c-icon[data-material-symbol="keyboard_arrow_right"]:not([data-mqe-material-symbol-svg])',
            $css,
        );
        static::assertStringContainsString(rawurlencode($svg), $css);
        static::assertStringNotContainsString('keyboard_arrow_left', $css);
        static::assertStringStartsWith('@supports ', $css);
    }
}
