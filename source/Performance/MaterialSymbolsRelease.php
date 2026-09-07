<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsRelease
{
    public static function resolve(string $root): ?string
    {
        $release = realpath(rtrim($root, '/') . '/current');
        $releases = realpath(rtrim($root, '/') . '/releases');
        if ($release === false || $releases === false || dirname($release) !== $releases) {
            return null;
        }

        return $release;
    }
}
