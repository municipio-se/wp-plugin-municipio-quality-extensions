<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

use RuntimeException;
use WP_CLI;

final class NavigationIndexCommand
{
    /** Inspect the current site's navigation index and migration record. */
    public function status(): void
    {
        $this->run('status');
    }

    /** Create or adopt the navigation index for the current site. Use --url to select a multisite blog. */
    public function enable(): void
    {
        $this->run('enable');
    }

    /** Remove only an unchanged plugin-owned index and clear the current site's opt-in. */
    public function disable(): void
    {
        $this->run('disable');
    }

    /**
     * Resolve WordPress's current blog connection at the CLI boundary.
     * @mago-expect lint:no-global
     */
    private function run(string $operation): void
    {
        try {
            $result = (new NavigationIndex($GLOBALS['wpdb']))->{$operation}();
            WP_CLI::line(wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } catch (RuntimeException $error) {
            WP_CLI::error($error->getMessage());
        }
    }
}
