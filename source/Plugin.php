<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions;

use MunicipioQualityExtensions\WebStandards\LateStyleHoisting;

final class Plugin
{
    public function register(): void
    {
        add_action('after_setup_theme', [new LateStyleHoisting(), 'register']);
    }
}
