<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Performance;

use MunicipioQualityExtensions\Performance\MaterialSymbolsFontStyles;
use MunicipioQualityExtensions\Performance\MaterialSymbolsStoreInterface;
use MunicipioQualityExtensions\Performance\MaterialSymbolsSvg;
use MunicipioQualityExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class MaterialSymbolsFontStylesTest extends TestCase
{
    public function testFontRemovalRequiresEnabledSvgAndExplicitOptOut(): void
    {
        WordPressState::reset();
        $store = $this->createMock(MaterialSymbolsStoreInterface::class);
        (new MaterialSymbolsSvg(false, $store, false))->register();
        static::assertSame([], WordPressState::$filters);
        (new MaterialSymbolsSvg(true, $store, false))->register();
        static::assertContains('style_loader_tag', array_column(WordPressState::$filters, 'hook'));
    }

    public function testRemovesFontDownloadsButPreservesLayout(): void
    {
        $layout = '.material-symbols-rounded{font-family:Material Symbols Rounded;line-height:1;display:inline-block}';
        static::assertSame(
            $layout,
            MaterialSymbolsFontStyles::withoutFontFace('@font-face{font-family:Material Symbols Rounded;src:url(font.woff2)}'
            . $layout),
        );
    }

    public function testUnknownOrUnsafeStylesheetsKeepOriginalLink(): void
    {
        foreach (['.icon{}', '@font-face{unexpected{nested}}', '@font-face{} </style><script>'] as $css) {
            static::assertNull(MaterialSymbolsFontStyles::withoutFontFace($css));
        }
    }
}
