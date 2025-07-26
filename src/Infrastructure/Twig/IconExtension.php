<?php

namespace App\Infrastructure\Twig;

use Symfony\Component\Asset\Package;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class IconExtension extends AbstractExtension
{
    public function __construct(private readonly Package $package)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('svg_icon', [$this, 'generateHtmlSvgForIcon'], ['is_safe' => ['html']]),
        ];
    }

    /** @param array{w: int, h: int}|array{} $options */
    public function generateHtmlSvgForIcon(string $iconName, array $options = []): string
    {
        $attrs = '';
        if (array_key_exists('w', $options)) {
            $attrs = " width=\"{$options['w']}px\"";
        }
        if (array_key_exists('h', $options)) {
            $attrs = " height=\"{$options['h']}px\"";
        }

        return <<<HTML
            <object type="image/svg+xml" class="icon icon-{$iconName}"{$attrs} data="{$this->package->getUrl('build/images/icons/'.$iconName.'.svg')}"></object>
        HTML;
    }
}
