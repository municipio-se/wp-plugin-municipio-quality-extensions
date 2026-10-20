<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions;

use MunicipioQualityExtensions\Cards\LinkedCards;
use MunicipioQualityExtensions\Headings\AccordionHeadings;
use MunicipioQualityExtensions\Headings\PostsHeadings;
use MunicipioQualityExtensions\Performance\MaterialSymbolsSvgFactory;
use MunicipioQualityExtensions\Performance\NavigationIndexCommand;
use MunicipioQualityExtensions\WebStandards\LateStyleHoisting;

final class Plugin
{
    public function register(): void
    {
        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('municipio-quality navigation-index', new NavigationIndexCommand());
        }
        add_action('after_setup_theme', [new LateStyleHoisting(), 'register']);
        MaterialSymbolsSvgFactory::create()->register();
        (new LinkedCards(dirname(__DIR__) . '/municipio-quality-extensions.php'))->register();
        (new PostsHeadings())->register();
        (new AccordionHeadings())->register();
    }
}
