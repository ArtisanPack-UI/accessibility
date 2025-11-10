<?php

namespace ArtisanPack\Accessibility\Plugins\Contracts;

interface AnalysisToolPluginInterface
{
    /**
     * Perform analysis and return a Report structure.
     * For simplicity, both input and output are arrays or value objects defined here.
     *
     * @param array $subject Data to analyze (e.g., project or document data).
     */
    public function analyze(array $subject, Context $context): Report;
}
