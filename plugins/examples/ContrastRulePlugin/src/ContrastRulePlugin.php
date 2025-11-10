<?php

namespace Plugins\Examples\ContrastRulePlugin;

use ArtisanPack\Accessibility\Core\WcagValidator;
use ArtisanPack\Accessibility\Plugins\Contracts\AccessibilityRulePluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\Capability;
use ArtisanPack\Accessibility\Plugins\Contracts\Context;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginMetadata;
use ArtisanPack\Accessibility\Plugins\Contracts\ResultSet;

class ContrastRulePlugin implements PluginInterface, AccessibilityRulePluginInterface
{
    private ?Context $context = null;

    public function getMetadata(): PluginMetadata
    {
        return new PluginMetadata(
            id: 'example.contrast_rule',
            name: 'Contrast Rule Plugin',
            version: '0.1.0',
            description: 'Provides a basic contrast accessibility rule.',
            author: 'Example',
            capabilities: [Capability::ACCESSIBILITY_RULE]
        );
    }

    public function initialize(Context $context): void
    {
        $this->context = $context;
    }

    public function start(): void {}

    public function stop(): void {}

    public function destroy(): void {}

    public function getRules(): iterable
    {
        return ['contrast.basic'];
    }

    public function evaluate(array $data, Context $context): ResultSet
    {
        $results = new ResultSet();
        $fg = $data['foreground'] ?? null;
        $bg = $data['background'] ?? null;
        $level = $data['level'] ?? 'AA';
        $large = (bool)($data['large'] ?? false);

        if (!is_string($fg) || !is_string($bg)) {
            $results->add('contrast.basic', 'error', 'foreground/background must be strings');
            return $results;
        }
        $validator = new WcagValidator();
        $ok = $validator->checkContrast($fg, $bg, $level, $large);
        if (!$ok) {
            $results->add('contrast.basic', 'error', 'Insufficient contrast', [
                'foreground' => $fg,
                'background' => $bg,
                'level' => $level,
                'large' => $large,
            ]);
        }
        return $results;
    }
}
