<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Support;

final class WordPressState
{
    /** @var array<int, array{hook: string, callback: callable, priority: int, acceptedArgs: int}> */
    public static array $filters = [];
    /** @var array<int, array{hook: string, callback: callable, priority: int, acceptedArgs: int}> */
    public static array $actions = [];
    public static bool $blockTheme = false;
    public static int $bufferStarts = 0;
    /** @var array<string, mixed> */
    public static array $themeMods = [];
    public static int $themeModWrites = 0;
    public static array $styles = [];
    /** @var array<string, string> */
    public static array $inlineStyles = [];
    public static string $wordpressVersion = '6.9.4';

    public static function reset(): void
    {
        self::$filters = [];
        self::$actions = [];
        self::$blockTheme = false;
        self::$bufferStarts = 0;
        self::$themeMods = [];
        self::$themeModWrites = 0;
        self::$styles = [];
        self::$inlineStyles = [];
        self::$wordpressVersion = '6.9.4';
    }
}
