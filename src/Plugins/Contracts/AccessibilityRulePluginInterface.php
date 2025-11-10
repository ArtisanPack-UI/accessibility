<?php

namespace ArtisanPack\Accessibility\Plugins\Contracts;

interface AccessibilityRulePluginInterface
{
    /**
     * Returns an iterable of rule identifiers provided by this plugin.
     * Example: ["contrast.basic", "links.underline"]
     *
     * @return iterable<string>
     */
    public function getRules(): iterable;

    /**
     * Evaluate a given data payload (shape defined by host) against plugin's rules.
     * For simplicity, we'll use array input and output.
     *
     * @param array $data Arbitrary structured data (e.g., DOM node info, colors, attributes).
     * @param Context $context Execution context for logging/config.
     * @return ResultSet Aggregated evaluation results.
     */
    public function evaluate(array $data, Context $context): ResultSet;
}
