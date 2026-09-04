<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Support;

final class WordPressState
{
    /** @var array<int, array{hook: string, callback: callable, priority: int, acceptedArgs: int}> */
    public static array $filters = [];
    public static bool $blockTheme = false;
    public static int $bufferStarts = 0;
    public static string $wordpressVersion = '6.9.4';

    public static function reset(): void
    {
        self::$filters = [];
        self::$blockTheme = false;
        self::$bufferStarts = 0;
        self::$wordpressVersion = '6.9.4';
    }
}
