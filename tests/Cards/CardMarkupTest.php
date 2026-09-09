<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Cards;

use MunicipioQualityExtensions\Cards\CardMarkup;
use PHPUnit\Framework\TestCase;

final class CardMarkupTest extends TestCase
{
    private function card(): array
    {
        return [
            'context' => ['module.posts.index', 'component.card'],
            'heading' => 'News &amp; updates',
            'link' => '/news/',
            'content' => '<a href="/other/">Other</a>',
            'classList' => [],
            'attributeList' => ['target' => '_blank', 'rel' => 'noopener', 'aria-label' => 'Old label'],
        ];
    }

    public function testMovesDestinationAndPreservesSecondaryContent(): void
    {
        $result = (new CardMarkup())->transform($this->card(), 'Läs mer', true);
        static::assertSame('', $result['link']);
        static::assertStringContainsString('href="/news/"', $result['heading']);
        static::assertStringContainsString('target="_blank" rel="noopener"', $result['heading']);
        static::assertStringContainsString('News &amp; updates', $result['heading']);
        static::assertSame([], $result['attributeList']);
        static::assertSame($this->card()['content'], $result['content']);
        static::assertContains('qx-card--underline', $result['classList']);
    }

    public function testUnknownAndSegmentContextsAreUntouched(): void
    {
        foreach ([
            [],
            ['module.manual-input.segment'],
            ['module.posts.index', 'module.sections.card'],
            ['unknown'],
        ] as $context) {
            $data = array_replace($this->card(), ['context' => $context]);
            static::assertSame($data, (new CardMarkup())->transform($data, 'Läs mer', true));
        }
    }

    public function testDefaultButtonsOnly(): void
    {
        foreach (['', 'Läs mer', " Läs\u{00a0}mer ", 'Ansök om plats'] as $text) {
            $data = array_replace($this->card(), [
                'context' => ['module.manual-input.card'],
                'link' => '',
                'buttons' => [['text' => $text, 'href' => '/apply/', 'target' => '_blank']],
            ]);
            $result = (new CardMarkup())->transform($data, 'Läs mer', false);
            if ($text === 'Ansök om plats') {
                static::assertSame($data, $result);
            } else {
                static::assertSame([], $result['buttons']);
                static::assertStringContainsString('href="/apply/"', $result['heading']);
                static::assertContains('qx-card--no-underline', $result['classList']);
            }
        }
    }

    public function testUnsafeOrAmbiguousCardsRemainUnchanged(): void
    {
        foreach ([
            ['heading' => ''],
            ['heading' => '&nbsp;'],
            ['heading' => '<a href="/other">Other</a>'],
            ['link' => 'javascript:alert(1)'],
            ['collapsible' => true],
            ['slot' => 'custom'],
            ['attributeList' => ['onclick' => 'openDialog()']],
            ['attributeList' => ['tabindex' => '0']],
            ['attributeList' => ['href' => '/different/']],
            ['link' => '', 'buttons' => [['href' => '/one/'], ['href' => '/two/']]],
            [
                'link' => '',
                'buttons' => [['href' => '/one/', 'text' => '', 'attributeList' => ['aria-controls' => 'dialog']]],
            ],
        ] as $override) {
            $data = array_replace($this->card(), $override);
            static::assertSame($data, (new CardMarkup())->transform($data, 'Läs mer', true));
        }
    }

    public function testNavigationAndEscaping(): void
    {
        $data = array_replace($this->card(), [
            'context' => ['module.navigation.cards'],
            'heading' => '"Title" <script>bad</script>',
        ]);
        $result = (new CardMarkup())->transform($data, 'Läs mer', true);
        static::assertStringContainsString('&quot;Title&quot;', $result['heading']);
        static::assertStringNotContainsString('<script>', $result['heading']);
    }
}
