<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

use RuntimeException;
use wpdb;

final class NavigationIndex
{
    public const OPTION = 'municipio_qx_navigation_index';
    public const NAME = 'municipio_qx_navigation';
    public const VERSION = 1;

    private readonly NavigationIndexSchema $schema;
    private readonly NavigationIndexState $state;

    public function __construct(wpdb $db)
    {
        $this->schema = new NavigationIndexSchema($db);
        $this->state = new NavigationIndexState();
    }

    public function status(): array
    {
        $state = $this->state->load();
        $indexes = $this->schema->indexes();
        return [
            'table' => $this->schema->tableName(),
            'enabled' =>
                $state['version'] === self::VERSION && NavigationIndexSchema::matches($indexes[$state['index']] ?? []),
            'migration' => $state,
            'matching_indexes' => array_keys(array_filter($indexes, NavigationIndexSchema::matches(...))),
        ];
    }

    public function enable(): array
    {
        return $this->schema->locked(function (): array {
            $state = $this->state->load();
            $indexes = $this->schema->indexes();
            $matches = array_filter($indexes, NavigationIndexSchema::matches(...));
            $name = array_key_exists(self::NAME, $matches) ? self::NAME : array_key_first($matches);
            if ($name !== null) {
                $owned = NavigationIndexState::owns($state, $name);
                $this->state->save(['version' => self::VERSION, 'index' => $name, 'owned' => $owned]);
                return $this->status();
            }
            if (($indexes[self::NAME] ?? null) !== null) {
                throw new RuntimeException('The reserved index exists with another definition or is disabled.');
            }

            // Persist intent before non-transactional DDL, so failure cannot appear as a completed migration.
            $this->state->save(['version' => 0, 'index' => self::NAME, 'owned' => false, 'pending' => true]);
            // Navigation visibility uses short keys and boolean values; prefixes keep this opt-in index small.
            $this->schema->alter('ADD INDEX `' . self::NAME . '` (meta_key(32), meta_value(1), post_id)');
            if (!NavigationIndexSchema::matches($this->schema->indexes()[self::NAME] ?? [])) {
                throw new RuntimeException('The new index could not be verified. Run status before retrying.');
            }
            $this->state->save(['version' => self::VERSION, 'index' => self::NAME, 'owned' => true]);
            return $this->status();
        });
    }

    public function disable(): array
    {
        return $this->schema->locked(function (): array {
            $state = $this->state->load();
            if ($state['owned']) {
                if ($state['index'] !== self::NAME) {
                    throw new RuntimeException('Invalid ownership record; no index was removed.');
                }
                $columns = $this->schema->indexes()[self::NAME] ?? null;
                if ($columns !== null) {
                    if (!NavigationIndexSchema::matches($columns)) {
                        throw new RuntimeException('The owned index changed or was disabled; refusing to remove it.');
                    }
                    $this->schema->alter('DROP INDEX `' . self::NAME . '`');
                }
            }
            $this->state->clear();
            return $this->status();
        });
    }
}
