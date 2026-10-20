<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Headings;

use MunicipioQualityExtensions\Headings\PostsHeadings;
use MunicipioQualityExtensions\Headings\PostsMarkup;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PostsHeadingsTest extends TestCase
{
    public static function presentations(): array
    {
        return [
            ['c-card',             'c-card__heading'],
            ['c-block',            'c-block__heading'],
            ['c-box',              'c-box__heading'],
            ['c-newsitem',         'c-newsitem__heading'],
            ['c-segment',          'c-segment__title'],
            ['c-collection__item', ''],
        ];
    }

    #[DataProvider('presentations')]
    public function testOnlyTheOwnedTitleTagsChange(string $component, string $class): void
    {
        $title =
            '<h2 class="c-typography c-typography__variant--h4 '
            . $class
            . '" id="post-12-99-title"><a href="/å?x=1&amp;y=2" target="_blank" rel="noopener">'
            . 'Åäö &amp; <em>rubrik</em></a></h2>';
        $prefix =
            '<div class="modularity-mod-posts"><a href="/edit?post=12&action=edit&is_inline=true">Edit</a><h2 id="mod-posts-12-label">Modul</h2>'
            . '<svg viewBox="0 0 24 24"><path d="M0 0"/></svg><div class="'
            . $component
            . '">';
        $suffix = '<div class="c-typography"><h2>Innehåll</h2></div></div></div>';
        $markup = $prefix . $title . $suffix;
        $expected = $prefix . str_replace(['<h2 ', '</h2>'], ['<h3 ', '</h3>'], $title) . $suffix;
        $transformer = new PostsMarkup();
        static::assertSame($expected, $transformer->transform($markup));
        static::assertSame($expected, $transformer->transform($expected));
        $hidden = str_replace('<h2 id="mod-posts-12-label">Modul</h2>', '', $expected);
        static::assertSame(
            str_replace('<h2 id="mod-posts-12-label">Modul</h2>', '', $markup),
            $transformer->transform($hidden),
        );
    }

    public function testForeignTitlesAndAmbiguousMarkupAreUntouched(): void
    {
        $transformer = new PostsMarkup();
        $title = '<h2 class="c-card__heading">Title</h2>';
        foreach ([
            $title,
            '<div class="c-block">' . $title . '</div>',
            '<div class="c-modal"><div class="c-card">' . $title . '</div></div>',
            '<div class="c-accordion"><div class="c-card">' . $title . '</div></div>',
            '<div class="modularity-mod-text"><div class="c-card">' . $title . '</div></div>',
            '<div class="modularity-mod-posts"><div class="modularity-mod-posts"><div class="c-card">'
                . $title
                . '</div></div></div>',
            '<div class="c-card"><div class="c-typography">' . $title . '</div></div>',
            '<h2 id="mod-posts-2-label">Duplicate</h2><div class="c-card">' . $title . '</div>',
            '<div class="c-card"><h2 class="c-card__heading"></h2></div>',
            '<div class="c-card"><h2 class="c-card__heading">Malformed</h3></div>',
        ] as $case) {
            $html = '<h2 id="mod-posts-1-label">Module</h2>' . $case;
            static::assertSame($html, $transformer->transform($html), $case);
        }
    }

    public function testSegmentCardTitleBelongsToSegment(): void
    {
        $html =
            '<h2 id="mod-posts-1-label">Module</h2><section class="c-segment">'
            . '<div class="c-card c-segment__content-card"><div class="c-card__paint-container">'
            . '<h2 class="c-segment__title">Post</h2>'
            . '<div class="c-typography c-segment__text"><h2>Content</h2></div>'
            . '</div></div></section>';
        static::assertSame(
            str_replace('<h2 class="c-segment__title">Post</h2>', '<h3 class="c-segment__title">Post</h3>', $html),
            (new PostsMarkup())->transform($html),
        );
    }

    public function testRawTextAndLibxmlStateArePreserved(): void
    {
        $html =
            '<h2 id="mod-posts-1-label">Module</h2><script>const example = "text";</script>'
            . '<div class="c-card"><H2 class="c-card__heading">Post &#38; title</H2></div>';
        $previous = libxml_use_internal_errors(false);
        try {
            $actual = (new PostsMarkup())->transform($html);
            static::assertFalse(libxml_use_internal_errors());
            static::assertSame(str_replace(['<H2 class=', '</H2>'], ['<H3 class=', '</H3>'], $html), $actual);
            // HTML4 and HTML5 disagree on raw-text end tags: do not guess offsets.
            $ambiguous = str_replace('"text"', '"<h2>example</h2>"', $html);
            static::assertSame($ambiguous, (new PostsMarkup())->transform($ambiguous));
        } finally {
            libxml_use_internal_errors($previous);
        }
    }

    public function testAdapterSkipsAccordionAndCustomTemplates(): void
    {
        $html = '<h2 id="mod-posts-1-label">Module</h2><div class="c-card"><h2 class="c-card__heading">Post</h2></div>';
        $feature = new PostsHeadings();
        foreach (['expandable-list', 'custom'] as $display) {
            static::assertSame($html, $feature->filter($html, (object) ['data' => ['posts_display_as' => $display]]));
        }
        static::assertStringContainsString('<h3 class=', $feature->filter($html, (object) ['data' => [
            'posts_display_as' => 'mixed',
        ]]));
        static::assertSame(
            ['other-cache', 'qx-posts-headings-v1'],
            $feature->cacheContext('other-cache', (object) ['post_type' => 'mod-posts']),
        );
        static::assertSame('other-cache', $feature->cacheContext('other-cache', (object) ['post_type' => 'mod-text']));
    }

    public function testBlockAdapterPreservesArgumentsReturnAndOuterBuffer(): void
    {
        $feature = new PostsHeadings();
        $args = [['data' => ['posts_display_as' => 'index']], '', true, 21, null];
        $settings = ['render_callback' => static function (...$received) use ($args) {
            self::assertSame($args, $received);
            echo
                '<h2 id="mod-posts-block-label">Module</h2><div class="c-card"><h2 class="c-card__heading">Post</h2></div>'
            ;
            return 'result';
        }];
        static::assertSame($settings, $feature->blockSettings($settings, 'text'));
        $callback = $feature->blockSettings($settings, 'posts')['render_callback'];
        ob_start();
        try {
            echo 'outer';
            static::assertSame('result', $callback(...$args));
            static::assertStringContainsString('outer<h2', ob_get_contents());
            static::assertStringContainsString('<h3 class=', ob_get_contents());
        } finally {
            ob_end_clean();
        }
        $callback = $feature->blockSettings(['render_callback' => static function () {
            echo 'discard';
            throw new RuntimeException('render failure');
        }], 'posts')['render_callback'];
        $level = ob_get_level();
        try {
            $callback([]);
            static::fail('Expected callback exception');
        } catch (RuntimeException $exception) {
            static::assertSame('render failure', $exception->getMessage());
            static::assertSame($level, ob_get_level());
        }
    }

    public function testBlockCacheReceivesTheOriginalSerializableCallback(): void
    {
        $callback = [new BlockRenderer(), 'render'];
        $settings = (new PostsHeadings())->blockSettings(['render_callback' => $callback], 'posts');
        ob_start();
        try {
            $serialized = $settings['render_callback']([
                'data' => [],
                'render_callback' => $settings['render_callback'],
            ]);
            static::assertSame(serialize(['data' => [], 'render_callback' => $callback]), $serialized);
        } finally {
            ob_end_clean();
        }
    }
}
