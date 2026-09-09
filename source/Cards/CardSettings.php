<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Cards;

final class CardSettings
{
    public const ENABLED = 'municipio_qx_linked_cards_enabled';
    public const UNDERLINE = 'municipio_qx_linked_cards_underline';
    public const VERSION = 'municipio_qx_linked_cards_migration_version';
    public const SECTION = 'municipio_customizer_section_component_card';

    public static function boolean(mixed $value): ?bool
    {
        return match ($value) {
            true, 1, '1', 'true' => true,
            false, 0, '0', 'false', '' => false,
            default => null,
        };
    }

    public static function sanitize(mixed $value): bool
    {
        return self::boolean($value) ?? false;
    }

    /** Return only writes, preserving explicit target values and the legacy source. */
    public static function migration(array $mods): array
    {
        $writes = [];
        if (!array_key_exists(self::ENABLED, $mods)) {
            $source = self::boolean($mods['card_mxui_enabled'] ?? null);
            if ($source === null) {
                return [];
            }
            $writes[self::ENABLED] = $source;
        }
        if ((int) ($mods[self::VERSION] ?? 0) < 1) {
            $writes[self::VERSION] = 1;
        }
        return $writes;
    }

    public function migrate(): void
    {
        // Read persisted data, not Customizer preview filters. Do not memoize across blogs.
        $mods = get_option('theme_mods_' . get_option('stylesheet'), []);
        foreach (self::migration(is_array($mods) ? $mods : []) as $key => $value) {
            set_theme_mod($key, $value);
        }
    }

    public function enabled(): bool
    {
        return self::boolean(get_theme_mod(self::ENABLED, false)) === true;
    }

    public function underline(): bool
    {
        return self::boolean(get_theme_mod(self::UNDERLINE, true)) ?? true;
    }

    public function register(object $section): void
    {
        if (!method_exists($section, 'getID') || $section->getID() !== self::SECTION) {
            return;
        }
        $field = \Municipio\Customizer\KirkiField::class;
        if (!class_exists($field)) {
            return;
        }
        foreach ([
            self::ENABLED => [__('Use heading links for cards', 'municipio-quality-extensions'), false],
            self::UNDERLINE => [__('Underline card heading links', 'municipio-quality-extensions'), true],
        ] as $key => [$label, $default]) {
            $field::addField([
                'type' => 'checkbox_switch',
                'settings' => $key,
                'label' => $label,
                'section' => self::SECTION,
                'default' => $default,
                'priority' => 30,
                'sanitize_callback' => [self::class, 'sanitize'],
                'transport' => 'refresh',
            ]);
        }
    }
}
