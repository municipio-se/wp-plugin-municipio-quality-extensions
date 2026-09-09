<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Cards;

final class CardMarkup
{
    public function transform(array $data, string $standardText, bool $underline): array
    {
        if (!CardEligibility::supports($data)) {
            return $data;
        }
        $heading = $data['heading'];
        $attributes = $data['attributeList'] ?? [];
        if (!is_array($attributes) || CardLink::hasAction($attributes)) {
            return $data;
        }
        $href = $data['link'] ?? '';
        $buttons = $data['buttons'] ?? [];
        $removeButton = false;
        if (in_array($href, ['', false], true)) {
            $button = StandardButton::select($buttons, $standardText);
            if ($button === null) {
                return $data;
            }
            $href = $button['href'] ?? '';
            $attributes = CardLink::buttonAttributes($button, $attributes);
            $removeButton = true;
        }
        // Fail closed for non-navigation URLs, rather than turning actions into links.
        if (!CardLink::validUrl($href)) {
            return $data;
        }
        if (($attributes['href'] ?? $href) !== $href) {
            return $data;
        }
        $data['heading'] = HeadingLink::render($heading, $href, $attributes);
        $data['attributeList'] = HeadingLink::wrapperAttributes($attributes);
        $data['link'] = '';
        $data['linkText'] = '';
        $data['classList'] = array_merge($data['classList'] ?? [], [
            'qx-card',
            $underline ? 'qx-card--underline' : 'qx-card--no-underline',
        ]);
        if ($removeButton) {
            $data['buttons'] = [];
        }
        return $data;
    }
}
