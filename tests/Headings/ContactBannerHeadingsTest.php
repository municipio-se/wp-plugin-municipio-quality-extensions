<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Headings;

use MunicipioQualityExtensions\Headings\ContactBannerHeadings;
use PHPUnit\Framework\TestCase;

final class ContactBannerHeadingsTest extends TestCase
{
    private function markup(string $content, bool $visible = true, string $title = 'Contact'): string
    {
        return (
            '<div id="contact" class="mod-contact-banner">'
            . ($visible ? '<h2 class="mod-contact-banner__title">Module</h2>' : '')
            . '<h3 class="mod-contact-banner__hours-title c-typography__variant--h4">Hours</h3>'
            . '<div class="c-card"><div class="c-card card-item-0"><div class="c-card__header">'
            . '<i data-icon="mail"></i><h3 id="mod-contactbanner-42-0" class="c-typography__variant--h3">'
            . $title
            . '</h3></div><div class="c-card__body">'
            . $content
            . '</div><a href="tel:123">Call</a></div></div></div>'
        );
    }

    private function prepare(string $content): string
    {
        return (new ContactBannerHeadings())->viewData(['ctaList' => [(object) [
            'content' => $content,
        ]]])['ctaList'][0]->content;
    }

    public function testLevelsTypographyEmptyTitlesAndIdempotence(): void
    {
        $extension = new ContactBannerHeadings();
        foreach ([true, false] as $visible) {
            foreach (['Contact', '', '  ', '&nbsp;'] as $title) {
                $content = $this->prepare('<h2 id="first">Åäö &amp; <em>text</em></h2><h4>Sub</h4><h6>Deep</h6>');
                $html = $this->markup($content, $visible, $title);
                $actual = $extension->filter($html);
                $base = $visible ? 3 : 2;
                $level = $base + (int) ($title === 'Contact');
                static::assertStringContainsString('<h' . $level . ' data-qx-contact-depth="0" id="first"', $actual);
                static::assertStringContainsString(
                    '<h' . min(6, $level + 2) . ' data-qx-contact-depth="2">Sub',
                    $actual,
                );
                static::assertStringContainsString('<h6 data-qx-contact-depth="4">Deep', $actual);
                static::assertStringContainsString(
                    '<h' . $base . ' class="mod-contact-banner__hours-title c-typography__variant--h4">',
                    $actual,
                );
                static::assertStringContainsString('<i data-icon="mail"></i>', $actual);
                static::assertStringContainsString('<a href="tel:123">Call</a>', $actual);
                if ($title === 'Contact') {
                    static::assertStringContainsString(
                        '<h' . $base . ' id="mod-contactbanner-42-0" class="c-typography__variant--h3">',
                        $actual,
                    );
                } else {
                    static::assertStringNotContainsString('mod-contactbanner-42-0', $actual);
                }
                static::assertSame($actual, $extension->filter($actual));
            }
        }
    }

    public function testSourceObjectsAndUnownedContentArePreserved(): void
    {
        $source =
            '<h2>Main</h2><div class="c-card"><h1>Embedded</h1></div>'
            . '<div class="modularity-mod-posts"><h1>Module</h1></div><dialog><h1>Dialog</h1></dialog>';
        $item = (object) ['content' => $source];
        $extension = new ContactBannerHeadings();
        $prepared = $extension->viewData(['ctaList' => [$item]]);
        static::assertSame($source, $item->content);
        static::assertNotSame($item, $prepared['ctaList'][0]);
        $actual = $extension->filter($this->markup($prepared['ctaList'][0]->content));
        static::assertStringContainsString('<h4 data-qx-contact-depth="0">Main</h4>', $actual);
        static::assertStringContainsString('<div class="c-card"><h1>Embedded</h1></div>', $actual);
        static::assertStringContainsString('<div class="modularity-mod-posts"><h1>Module</h1></div>', $actual);
        static::assertStringContainsString('<dialog><h1>Dialog</h1></dialog>', $actual);
    }

    public function testTitleToggleRetainsOriginalDepthAfterClipping(): void
    {
        $extension = new ContactBannerHeadings();
        $html = $extension->filter($this->markup($this->prepare('<h1>A</h1><h4>B</h4><h6>C</h6>')));
        $label = '<h2 class="mod-contact-banner__title">Module</h2>';
        $hidden = $extension->filter(str_replace($label, '', $html));
        static::assertStringContainsString('<h3 data-qx-contact-depth="0">A', $hidden);
        static::assertSame(
            $html,
            $extension->filter(str_replace(
                '<div id="contact" class="mod-contact-banner">',
                '<div id="contact" class="mod-contact-banner">' . $label,
                $hidden,
            )),
        );
    }

    public function testBlockWithoutModuleIdStillRecognizesTemplateTitles(): void
    {
        $extension = new ContactBannerHeadings();
        foreach (['Contact', ''] as $title) {
            $html = str_replace(
                'mod-contactbanner-42-0',
                'mod-contactbanner--0',
                $this->markup($this->prepare('<h2>Main</h2>'), false, $title),
            );
            $result = $extension->filter($html);
            static::assertStringContainsString(
                '<h' . ($title === '' ? 2 : 3) . ' data-qx-contact-depth="0">Main',
                $result,
            );
            static::assertSame($title !== '', str_contains($result, 'mod-contactbanner--0'));
        }
    }

    public function testMalformedAndAmbiguousMarkupFailsClosed(): void
    {
        $extension = new ContactBannerHeadings();
        foreach ([
            '<h2>Broken</h3>',
            '<h2 class="mod-contact-banner__title">Another module title</h2>',
        ] as $content) {
            $html = $this->markup($content);
            // A label inside a body is foreign to the module's own label.
            if (str_contains($content, 'Another')) {
                $html = str_replace(
                    '<div id="contact" class="mod-contact-banner">',
                    '<div id="contact" class="mod-contact-banner">' . $content,
                    $this->markup(''),
                );
            }
            static::assertSame($html, $extension->filter($html));
        }
    }

    public function testBlockWrapperVersionsCacheAndTransformsCacheHits(): void
    {
        $extension = new ContactBannerHeadings();
        $source = $this->markup($this->prepare('<h2>Main</h2>'), false);
        $seen = [];
        $callback = static function ($block) use (&$seen, $source) {
            $seen[] = $block;
            echo $source;
            return 'return-value';
        };
        $settings = $extension->blockSettings(['render_callback' => $callback], 'contact-banner');
        for ($i = 0; $i < 2; $i++) {
            ob_start();
            $result = $settings['render_callback'](['render_callback' => $settings['render_callback']]);
            $actual = ob_get_clean();
            static::assertSame('return-value', $result);
            static::assertStringContainsString('<h3 data-qx-contact-depth="0">Main', $actual);
            static::assertSame($callback, $seen[$i]['render_callback']);
            static::assertSame('qx-contact-banner-headings-v1', $seen[$i]['qx_contact_headings_version']);
        }
        static::assertSame(
            ['render_callback' => $callback],
            $extension->blockSettings(['render_callback' => $callback], 'posts'),
        );
        static::assertSame(['original'], $extension->cacheContext(['original'], (object) ['post_type' => 'mod-posts']));
        static::assertNotSame(
            ['original'],
            $extension->cacheContext(['original'], (object) ['post_type' => 'mod-contact-banner']),
        );
    }
}
