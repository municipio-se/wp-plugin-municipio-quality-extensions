<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Cards;

use MunicipioQualityExtensions\Cards\CardSettings;
use MunicipioQualityExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class CardSettingsTest extends TestCase
{
    public function testDefaultsAndExplicitFalse(): void
    {
        WordPressState::reset();
        $settings = new CardSettings();
        static::assertFalse($settings->enabled());
        static::assertTrue($settings->underline());
        WordPressState::$themeMods = [CardSettings::ENABLED => true, CardSettings::UNDERLINE => false];
        static::assertTrue($settings->enabled());
        static::assertFalse($settings->underline());
        WordPressState::reset();
        static::assertFalse($settings->enabled());
    }

    public function testMappingAndIdempotence(): void
    {
        foreach ([true, false, 1, 0, '1', '0', 'true', 'false'] as $source) {
            $mods = ['card_mxui_enabled' => $source];
            $writes = CardSettings::migration($mods);
            static::assertSame(CardSettings::boolean($source), $writes[CardSettings::ENABLED]);
            static::assertArrayNotHasKey(CardSettings::UNDERLINE, $writes);
            static::assertSame([], CardSettings::migration(array_replace($mods, $writes)));
            static::assertArrayNotHasKey('card_mxui_enabled', $writes);
        }
    }

    public function testExplicitTargetsWinIncludingFalse(): void
    {
        foreach ([true, false] as $target) {
            $writes = CardSettings::migration([CardSettings::ENABLED => $target, 'card_mxui_enabled' => !$target]);
            static::assertArrayNotHasKey(CardSettings::ENABLED, $writes);
        }
    }

    public function testAbsentInvalidAndLaterImport(): void
    {
        foreach ([[], ['card_mxui_enabled' => 'broken'], ['card_mxui_enabled' => []]] as $mods) {
            static::assertSame([], CardSettings::migration($mods));
        }
        static::assertTrue(
            CardSettings::migration([CardSettings::VERSION => 1, 'card_mxui_enabled' => true])[CardSettings::ENABLED],
        );
        static::assertFalse(CardSettings::boolean('false'));
        static::assertNull(CardSettings::boolean('broken'));
    }
}
