<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Cards;

use MunicipioQualityExtensions\Cards\CardSettings;
use MunicipioQualityExtensions\Cards\LinkedCards;
use MunicipioQualityExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class LinkedCardsTest extends TestCase
{
    protected function setUp(): void
    {
        WordPressState::reset();
    }

    public function testRegistrationDoesNotEnableOrMigrateOtherSites(): void
    {
        $feature = new LinkedCards(dirname(__DIR__, 2) . '/municipio-quality-extensions.php');
        $feature->register();
        $data = [
            'context' => ['module.manual-input.card'],
            'heading' => 'Rubrik',
            'buttons' => [['text' => 'Läs mer', 'href' => '/news/']],
        ];
        static::assertSame($data, $feature->filter($data));
        $feature->enqueue();
        static::assertSame([], WordPressState::$styles);
        static::assertContains('switch_blog', array_column(WordPressState::$actions, 'hook'));
        WordPressState::$themeMods = [CardSettings::ENABLED => true];
        static::assertStringContainsString('qx-card__primary-link', $feature->filter($data)['heading']);
        $feature->enqueue();
        static::assertArrayHasKey('municipio-qx-linked-cards', WordPressState::$styles);
        WordPressState::$themeMods = [CardSettings::ENABLED => false];
        static::assertSame($data, $feature->filter($data));
    }

    public function testMigrationRechecksCurrentBlogAndLaterImportsWithoutRepeatWrites(): void
    {
        $settings = new CardSettings();
        $settings->migrate();
        static::assertSame(0, WordPressState::$themeModWrites);
        WordPressState::$themeMods = ['card_mxui_enabled' => true];
        $settings->migrate();
        static::assertTrue($settings->enabled());
        $writes = WordPressState::$themeModWrites;
        $settings->migrate();
        static::assertSame($writes, WordPressState::$themeModWrites);
        WordPressState::$themeMods = ['card_mxui_enabled' => 'false'];
        $settings->migrate();
        static::assertFalse($settings->enabled());
        static::assertSame('false', WordPressState::$themeMods['card_mxui_enabled']);
        WordPressState::$themeMods = [CardSettings::ENABLED => false, CardSettings::VERSION => 2];
        $writes = WordPressState::$themeModWrites;
        $settings->migrate();
        static::assertSame($writes, WordPressState::$themeModWrites);
    }
}
