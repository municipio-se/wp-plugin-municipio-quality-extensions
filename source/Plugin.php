<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions;

use MunicipioQualityExtensions\Cards\LinkedCards;
use MunicipioQualityExtensions\Performance\MaterialSymbolsSvgFactory;
use MunicipioQualityExtensions\WebStandards\LateStyleHoisting;

final class Plugin
{
    public function register(): void
    {
        add_action('after_setup_theme', [new LateStyleHoisting(), 'register']);
        MaterialSymbolsSvgFactory::create()->register();
        (new LinkedCards(dirname(__DIR__) . '/municipio-quality-extensions.php'))->register();
    }
}
