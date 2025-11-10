<?php

namespace ArtisanPack\Accessibility\Plugins\Contracts;

enum Capability: string
{
    case COLOR_FORMAT = 'color_format';
    case ACCESSIBILITY_RULE = 'accessibility_rule';
    case ANALYSIS_TOOL = 'analysis_tool';
}
