<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Support;

final class OptionsState
{
    public static array $values = [];
    public static bool $writesFail = false;

    public static function reset(): void
    {
        self::$values = [];
        self::$writesFail = false;
    }
}
