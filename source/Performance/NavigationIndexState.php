<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

use RuntimeException;

final class NavigationIndexState
{
    public static function owns(array $state, string $name): bool
    {
        // An interrupted creation has uncertain ownership. Preserve its index on rollback.
        return (
            $state['version'] === NavigationIndex::VERSION
            && $state['owned']
            && $state['index'] === NavigationIndex::NAME
            && $name === NavigationIndex::NAME
        );
    }

    public function load(): array
    {
        $state = get_option(NavigationIndex::OPTION, []);
        if (!is_array($state)) {
            throw new RuntimeException('Invalid migration state.');
        }
        $state += ['version' => 0, 'index' => '', 'owned' => false];
        if (!is_int($state['version']) || $state['version'] > NavigationIndex::VERSION || $state['version'] < 0) {
            throw new RuntimeException(
                'Unknown migration state; use the matching plugin version before making changes.',
            );
        }
        if (!is_string($state['index']) || !is_bool($state['owned'])) {
            throw new RuntimeException('Invalid migration ownership record.');
        }
        return $state;
    }

    public function save(array $state): void
    {
        update_option(NavigationIndex::OPTION, $state, false);
        if (get_option(NavigationIndex::OPTION) !== $state) {
            throw new RuntimeException('Could not persist the migration state.');
        }
    }

    public function clear(): void
    {
        delete_option(NavigationIndex::OPTION);
        if (get_option(NavigationIndex::OPTION, null) !== null) {
            throw new RuntimeException('Could not clear the migration record. Run disable again.');
        }
    }
}
