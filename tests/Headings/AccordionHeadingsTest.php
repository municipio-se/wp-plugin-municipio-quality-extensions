<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Headings;

use MunicipioQualityExtensions\Headings\AccordionContent;
use MunicipioQualityExtensions\Headings\AccordionHeadings;
use MunicipioQualityExtensions\Headings\AccordionMarkup;
use PHPUnit\Framework\TestCase;

final class AccordionHeadingsTest extends TestCase
{
    private function module(string $content, bool $visible = true, string $type = 'mod-posts'): string
    {
        $label = $type === 'mod-posts'
            ? '<h2 id="mod-posts-12-label">Module</h2>'
            : '<div class="c-card__header"><h2>Module</h2></div>';
        return (
            '<div class="modularity-'
            . $type
            . '">'
            . ($visible ? $label : '')
            . '<div class="c-accordion"><section class="c-accordion__section" data-qx-accordion="1">'
            . '<button aria-controls="content"><h3 class="c-accordion__button-column c-typography__variant--h4">Row</h3></button>'
            . '<div id="content" class="c-accordion__content">'
            . $content
            . '</div></section></div></div>'
        );
    }

    public function testRelativeDepthClippingAndRepeatedProcessingPreserveAllOtherMarkup(): void
    {
        $source =
            '<h2 id="å"><a href="/?x=1&amp;y=2">Åäö <em>title</em></a></h2>'
            . '<h4 class="custom">Child</h4><h6 style="color:red">Deep</h6>';
        $prepared = (new AccordionContent())->prepare($source);
        foreach (['mod-posts', 'mod-manualinput'] as $type) {
            foreach ([true, false] as $visible) {
                $html = $this->module($prepared, $visible, $type);
                $expected = strtr(
                    $prepared,
                    $visible
                        ? ['<h2 ' => '<h4 ', '</h2>' => '</h4>', '<h4 ' => '<h6 ', '</h4>' => '</h6>']
                        : ['<h2 ' => '<h3 ', '</h2>' => '</h3>', '<h4 ' => '<h5 ', '</h4>' => '</h5>'],
                );
                // Opening and closing tag digits are the only permitted changes.
                $result = (new AccordionMarkup())->transform($html, $type);
                static::assertStringContainsString($expected, $result);
                static::assertSame($result, (new AccordionMarkup())->transform($result, $type));
                static::assertSame(
                    preg_replace('/(<\/?h)[1-6]/', '$1X', $html),
                    preg_replace('/(<\/?h)[1-6]/', '$1X', $result),
                );
            }
        }
    }

    public function testSourceDepthSurvivesAChangedModuleHeadingInCachedMarkup(): void
    {
        $prepared = (new AccordionContent())->prepare('<h2>A</h2><h4>B</h4><h6>C</h6>');
        $transformer = new AccordionMarkup();
        $visible = $transformer->transform($this->module($prepared), 'mod-posts');
        $hidden = str_replace('<h2 id="mod-posts-12-label">Module</h2>', '', $visible);
        $result = $transformer->transform($hidden, 'mod-posts');
        static::assertStringContainsString('<h5 data-qx-accordion-depth="2">B</h5>', $result);
        static::assertStringContainsString('<h6 data-qx-accordion-depth="4">C</h6>', $result);
    }

    public function testForeignContentDoesNotDetermineTheBaseOrChange(): void
    {
        $foreign =
            '<div class="modularity-mod-text"><h1>Nested</h1></div>'
            . '<div class="c-card"><h2 class="c-card__heading">Card</h2></div>'
            . '<div class="c-accordion"><h1>Nested accordion</h1></div>';
        $content = (new AccordionContent())->prepare($foreign . '<h4>Own</h4>');
        static::assertStringContainsString($foreign, $content);
        $result = (new AccordionMarkup())->transform($this->module($content, false), 'mod-posts');
        static::assertStringContainsString($foreign, $result);
        static::assertStringContainsString('<h3 data-qx-accordion-depth="0">Own</h3>', $result);
    }

    public function testMalformedAndUnpreparedMarkupIsUnchanged(): void
    {
        static::assertNull((new AccordionContent())->prepare('<h2>Broken</h3>'));
        $html = str_replace(' data-qx-accordion="1"', '', $this->module('<h2>Original</h2>', false));
        static::assertSame($html, (new AccordionMarkup())->transform($html, 'mod-posts'));
        $empty = str_replace('>Row</h3>', '></h3>', $this->module('<h2>Content</h2>', false));
        static::assertSame($empty, (new AccordionMarkup())->transform($empty, 'mod-posts'));
    }

    public function testViewDataUsesTheAccessCheckedOriginalAndVerifiesIdentity(): void
    {
        $post = new class {
            public string $postContentFiltered = '<h6>Lost depth</h6>';

            public function getId(): int
            {
                return 42;
            }

            public function __get(string $name): string
            {
                return '<h2>Original</h2><h6>Deep</h6>';
            }
        };
        $data = [
            'post_type' => 'mod-posts',
            'posts_display_as' => 'expandable-list',
            'posts' => [$post],
            'prepareAccordion' => [[
                'content' => $post->postContentFiltered,
                'attributeList' => ['data-js-item-id' => 42],
            ]],
        ];
        $result = (new AccordionHeadings())->viewData($data);
        static::assertStringContainsString(
            '<h2 data-qx-accordion-depth="0">Original</h2>',
            $result['prepareAccordion'][0]['content'],
        );
        static::assertStringContainsString('data-qx-accordion-depth="4"', $result['prepareAccordion'][0]['content']);
        static::assertSame('<h6>Lost depth</h6>', $post->postContentFiltered);
        $data['prepareAccordion'][0]['attributeList']['data-js-item-id'] = 43;
        static::assertSame($data, (new AccordionHeadings())->viewData($data));
    }

    public function testManualPresentationScope(): void
    {
        $data = [
            'post_type' => 'mod-manualinput',
            'context' => ['module.manual-input.accordion'],
            'manualInputs' => [['content' => '<h4>Title</h4>', 'attributeList' => ['id' => 'row']]],
        ];
        $result = (new AccordionHeadings())->viewData($data);
        static::assertSame('row', $result['manualInputs'][0]['attributeList']['id']);
        static::assertSame('1', $result['manualInputs'][0]['attributeList']['data-qx-accordion']);
        $data['context'] = ['module.manual-input.card'];
        static::assertSame($data, (new AccordionHeadings())->viewData($data));
    }

    public function testUpstreamStrayQuoteIsToleratedWithoutChangingSource(): void
    {
        $html = str_replace(
            'data-qx-accordion="1"',
            'data-qx-accordion="1" data-component="accordion__item""',
            $this->module('', false),
        );
        $result = (new AccordionMarkup())->transform($html, 'mod-posts');
        static::assertStringContainsString('data-component="accordion__item""', $result);
        static::assertStringContainsString('<h2 class="c-accordion__button-column', $result);
    }

    public function testNullManualContentStillAdaptsRowHeading(): void
    {
        $data = [
            'post_type' => 'mod-manualinput',
            'context' => ['module.manual-input.accordion'],
            'manualInputs' => [['content' => null]],
        ];
        $result = (new AccordionHeadings())->viewData($data);
        static::assertSame('', $result['manualInputs'][0]['content']);
        static::assertSame('1', $result['manualInputs'][0]['attributeList']['data-qx-accordion']);
    }

    public function testBlockWrapperRestoresCallbackAndCleansBufferOnException(): void
    {
        $adapter = new AccordionHeadings();
        $html = $this->module((new AccordionContent())->prepare('<h2>Own</h2>'), false);
        $callback = static function ($block) use ($html) {
            static::assertTrue(is_callable($block['render_callback']));
            echo $html;
            return 42;
        };
        $settings = $adapter->blockSettings(['render_callback' => $callback], 'posts');
        ob_start();
        $result = $settings['render_callback']($settings);
        $output = ob_get_clean();
        static::assertSame(42, $result);
        static::assertStringContainsString('<h3 data-qx-accordion-depth="0">Own</h3>', $output);
        $level = ob_get_level();
        $settings = $adapter->blockSettings(['render_callback' => static function () {
            echo 'discard';
            throw new \RuntimeException('failure');
        }], 'manualinput');
        try {
            $settings['render_callback']([]);
            static::fail('Expected exception');
        } catch (\RuntimeException $error) {
            static::assertSame('failure', $error->getMessage());
            static::assertSame($level, ob_get_level());
        }
    }
}
