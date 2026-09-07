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
    public static string $wordpressVersion = '6.9.4';

    public static function reset(): void
    {
        self::$filters = [];
        self::$actions = [];
        self::$blockTheme = false;
        self::$bufferStarts = 0;
        self::$themeMods = [];
        self::$wordpressVersion = '6.9.4';
    }
}
