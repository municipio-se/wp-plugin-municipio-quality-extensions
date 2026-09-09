<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Cards;

final class LinkedCards
{
    public function __construct(
        private string $pluginFile,
        private CardSettings $settings = new CardSettings(),
    ) {}

    public function register(): void
    {
        add_action('init', [$this->settings, 'migrate'], 5);
        add_action('switch_blog', [$this->settings, 'migrate']);
        add_action('municipio_customizer_section_registered', [$this->settings, 'register']);
        add_filter('ComponentLibrary/Component/Card/Data', [$this, 'filter']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue'], 100);
    }

    public function filter(array $data): array
    {
        return $this->settings->enabled()
            ? (new CardMarkup())->transform($data, __('Read more', 'municipio'), $this->settings->underline())
            : $data;
    }

    public function enqueue(): void
    {
        if (!$this->settings->enabled()) {
            return;
        }
        $path = dirname($this->pluginFile) . '/assets/css/linked-cards.css';
        wp_enqueue_style(
            'municipio-qx-linked-cards',
            plugins_url('assets/css/linked-cards.css', $this->pluginFile),
            [],
            (string) filemtime($path),
        );
    }
}
