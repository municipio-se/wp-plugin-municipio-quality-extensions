<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Performance;

use MunicipioQualityExtensions\Performance\MaterialSymbolsEventStyles;
use MunicipioQualityExtensions\Performance\MaterialSymbolsStoreInterface;
use MunicipioQualityExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class MaterialSymbolsEventStylesTest extends TestCase
{
    public function testMissingIconsDoNotHideTheirFontFallback(): void
    {
        WordPressState::reset();
        $store = $this->createMock(MaterialSymbolsStoreInterface::class);
        $store->method('getIcon')->willReturn(null);
        static::assertSame('', MaterialSymbolsEventStyles::render($store));
    }

    public function testOnlyAvailableIconsAreReplacedUsingTheSiteVariant(): void
    {
        WordPressState::reset();
        WordPressState::$themeMods = ['icon_style' => 'sharp', 'icon_weight' => '600'];
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0z"/></svg>';
        $store = $this->createMock(MaterialSymbolsStoreInterface::class);
        $store
            ->expects(self::exactly(9))
            ->method('getIcon')
            ->willReturnCallback(static function (string $name, string $style, int $weight, int $fill) use (
                $svg,
            ): ?string {
                self::assertSame(['sharp', 600, 0], [$style, $weight, $fill]);
                return $name === 'chevron_left' ? $svg : null;
            });

        $css = MaterialSymbolsEventStyles::render($store);
        static::assertStringContainsString(rawurlencode($svg), $css);
        static::assertStringNotContainsString('<svg', $css);
        static::assertStringContainsString('.c-pagination__item--previous', $css);
        static::assertStringNotContainsString('.c-pagination__item--next', $css);
        static::assertStringContainsString(':not([data-mqe-material-symbol-svg])', $css);
        static::assertStringStartsWith('@supports ', $css);
    }

    public function testMissingOpenArrowDoesNotApplyClosedArrowToOpenDropdown(): void
    {
        WordPressState::reset();
        $store = $this->createMock(MaterialSymbolsStoreInterface::class);
        $store->method('getIcon')->willReturnCallback(static fn(string $name): ?string => $name
            === 'keyboard_arrow_down'
                ? '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z"/></svg>'
                : null);
        $css = MaterialSymbolsEventStyles::render($store);
        static::assertStringContainsString(
            '.modularity-event-index .c-dropdown:not(:has(> .c-dropdown__list)) > button',
            $css,
        );
        static::assertStringNotContainsString('.c-dropdown:has(', $css);
        static::assertStringNotContainsString('filter-keyword', $css);
    }
}
