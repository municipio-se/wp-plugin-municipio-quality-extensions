<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Performance;

use MunicipioQualityExtensions\Performance\MaterialSymbolsStoreInterface;
use MunicipioQualityExtensions\Performance\MaterialSymbolsSvg;
use MunicipioQualityExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MaterialSymbolsSvgTest extends TestCase
{
    private MaterialSymbolsStoreInterface&MockObject $store;

    protected function setUp(): void
    {
        WordPressState::reset();
        $this->store = $this->createMock(MaterialSymbolsStoreInterface::class);
    }

    public function testItDoesNotRegisterWhenDisabled(): void
    {
        (new MaterialSymbolsSvg(false, $this->store))->register();

        static::assertSame([], WordPressState::$filters);
        static::assertSame([], WordPressState::$actions);
    }

    public function testItRegistersComponentFiltersAndStyleActionsWhenEnabled(): void
    {
        (new MaterialSymbolsSvg(true, $this->store))->register();

        static::assertSame(
            ['ComponentLibrary/Component/Icon/Data', 'ComponentLibrary/Component/Icon/Attribute'],
            array_column(WordPressState::$filters, 'hook'),
        );
        static::assertSame(
            ['wp_enqueue_scripts', 'admin_enqueue_scripts'],
            array_column(WordPressState::$actions, 'hook'),
        );
    }

    public function testItInjectsASelectedSvgAndMarksTheIcon(): void
    {
        WordPressState::$themeMods = ['icon_style' => 'rounded', 'icon_weight' => '400'];
        $this->store
            ->expects(static::once())
            ->method('getIcon')
            ->with('search', 'rounded', 400, 0)
            ->willReturn('<svg viewBox="0 -960 960 960"><path d="M0 0"/></svg>');

        $result = (new MaterialSymbolsSvg(true, $this->store))->replaceIcon([
            'icon' => 'search',
            'filled' => null,
            'defaultFilled' => false,
            'classList' => ['c-icon'],
            'attributeList' => [],
        ]);

        static::assertStringContainsString('aria-hidden="true"', $result['svgElementFromFile']);
        static::assertNotContains('c-icon--svg-path', $result['classList']);
        static::assertArrayHasKey('data-mqe-material-symbol-svg', $result['attributeList']);
    }

    public function testItNeutralizesFontGeometryWithoutApplyingGenericSvgPathStyles(): void
    {
        (new MaterialSymbolsSvg(true, $this->store))->enqueueStyles();

        $styles = WordPressState::$inlineStyles['municipio-quality-extensions-material-symbols-svg'];
        static::assertStringContainsString('::after{content:none!important;display:none!important}', $styles);
        static::assertStringContainsString('inline-size:1em;block-size:1em', $styles);
        static::assertStringContainsString('inline-size:100%!important;block-size:100%!important', $styles);
        static::assertStringContainsString('path{fill:currentColor;stroke:none}', $styles);
    }

    public function testItPreservesFontFallbackWhenTheSvgIsMissing(): void
    {
        $data = ['icon' => 'missing'];
        $this->store->method('getIcon')->willReturn(null);

        static::assertSame($data, (new MaterialSymbolsSvg(true, $this->store))->replaceIcon($data));
    }

    public function testItRemovesTheFontGlyphAttributeOnlyFromReplacedIcons(): void
    {
        $feature = new MaterialSymbolsSvg(true, $this->store);

        static::assertSame(
            ['data-mqe-material-symbol-svg' => ''],
            $feature->filterAttributes([
                'data-mqe-material-symbol-svg' => '',
                'data-material-symbol' => 'search',
            ]),
        );
        static::assertSame(
            ['data-material-symbol' => 'search'],
            $feature->filterAttributes(['data-material-symbol' => 'search']),
        );
    }

    public function testItPreservesSerializedAttributesFromNestedRenderPaths(): void
    {
        $attributes = 'class="c-field" data-component="field"';

        static::assertSame($attributes, (new MaterialSymbolsSvg(true, $this->store))->filterAttributes($attributes));
    }
}
