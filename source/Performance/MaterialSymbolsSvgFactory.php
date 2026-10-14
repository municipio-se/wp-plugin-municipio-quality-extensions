<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsSvgFactory
{
    private const ENABLED_CONSTANT = 'MUNICIPIO_QUALITY_EXTENSIONS_MATERIAL_SYMBOLS_SVG_ENABLED';
    private const STORE_PATH_CONSTANT = 'MUNICIPIO_QUALITY_EXTENSIONS_MATERIAL_SYMBOLS_STORE_PATH';
    private const FONT_ENABLED_CONSTANT = 'MUNICIPIO_QUALITY_EXTENSIONS_MATERIAL_SYMBOLS_FONT_ENABLED';

    public static function create(): MaterialSymbolsSvg
    {
        $enabled = defined(self::ENABLED_CONSTANT) && constant(self::ENABLED_CONSTANT) === true;
        $storePath = defined(self::STORE_PATH_CONSTANT) ? (string) constant(self::STORE_PATH_CONSTANT) : '';

        $fontEnabled = !defined(self::FONT_ENABLED_CONSTANT) || constant(self::FONT_ENABLED_CONSTANT) !== false;
        return new MaterialSymbolsSvg(
            $enabled && $storePath !== '',
            new MaterialSymbolsStore($storePath),
            $fontEnabled,
        );
    }
}
