<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsSvgFactory
{
    private const ENABLED_CONSTANT = 'MUNICIPIO_QUALITY_EXTENSIONS_MATERIAL_SYMBOLS_SVG_ENABLED';
    private const STORE_PATH_CONSTANT = 'MUNICIPIO_QUALITY_EXTENSIONS_MATERIAL_SYMBOLS_STORE_PATH';

    public static function create(): MaterialSymbolsSvg
    {
        $enabled = defined(self::ENABLED_CONSTANT) && constant(self::ENABLED_CONSTANT) === true;
        $storePath = defined(self::STORE_PATH_CONSTANT) ? (string) constant(self::STORE_PATH_CONSTANT) : '';

        return new MaterialSymbolsSvg($enabled && $storePath !== '', new MaterialSymbolsStore($storePath));
    }
}
