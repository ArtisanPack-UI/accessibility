# Plugin Development Guide

This package supports third‑party plugins to extend functionality with custom color formats, accessibility rules, and analysis tools.

This guide explains how to build, register, and test a plugin.

## Concepts
- Plugin: a package providing capabilities via a manifest (plugin.json) or Composer metadata, implementing the published PluginInterface and any capability-specific interfaces.
- Capabilities:
  - color_format: provide parsing/serialization for color formats.
  - accessibility_rule: provide rules that evaluate content or color pairs.
  - analysis_tool: perform analyses and return reports.

## Contracts
Import from the host package:
- ArtisanPack\Accessibility\Plugins\Contracts\PluginInterface
- ArtisanPack\Accessibility\Plugins\Contracts\PluginMetadata
- ArtisanPack\Accessibility\Plugins\Contracts\Capability
- Capability-specific interfaces:
  - ColorFormatPluginInterface
  - AccessibilityRulePluginInterface
  - AnalysisToolPluginInterface

## Minimal Plugin (Conventional Directory)
Create a directory under `plugins/YourPlugin` with:

plugin.json
```
{
  "id": "vendor.your_plugin",
  "name": "Your Plugin",
  "version": "0.1.0",
  "entry": "Vendor\\YourPlugin\\Main",
  "capabilities": ["color_format"],
  "autoload": {
    "files": ["src/Main.php"]
  }
}
```

src/Main.php
```
<?php
namespace Vendor\YourPlugin;

use ArtisanPack\Accessibility\Plugins\Contracts\Capability;
use ArtisanPack\Accessibility\Plugins\Contracts\ColorFormatPluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\Context;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginInterface;
use ArtisanPack\Accessibility\Plugins\Contracts\PluginMetadata;

class Main implements PluginInterface, ColorFormatPluginInterface {
    public function getMetadata(): PluginMetadata {
        return new PluginMetadata(
            id: 'vendor.your_plugin',
            name: 'Your Plugin',
            version: '0.1.0',
            capabilities: [Capability::COLOR_FORMAT]
        );
    }
    public function initialize(Context $context): void {}
    public function start(): void {}
    public function stop(): void {}
    public function destroy(): void {}

    public function getSupportedFormats(): array { return ['hex']; }
    public function parse(string $input): string { return '#ffffff'; }
    public function serialize(string $hex, string $format): string { return $hex; }
}
```

## Composer Discovery (Preferred)
If distributing via Composer, set in your plugin's composer.json:
```
{
  "type": "artisanpack-ui-plugin",
  "extra": {
    "accessibility": {
      "plugins": [
        "Vendor\\YourPlugin\\Main"
      ]
    }
  }
}
```
Ensure PSR-4 autoload points to your source directory.

## Using PluginManager
```
use ArtisanPack\Accessibility\Plugins\PluginManager;
use ArtisanPack\Accessibility\Plugins\Contracts\Context;

$config = require __DIR__ . '/../config/plugins.php';
$context = new Context(['plugins' => $config]);
$manager = new PluginManager($context);
$manager->discoverAndRegister();

$hex = $manager->getColorFormatPluginFor('hex');
```

## Safety and Validation
- Required manifest fields: id, name, version, entry, capabilities.
- allowlist/denylist supported via config.
- Safe mode: set `plugins.safe_mode = true` to discover without activating.

## Examples
See `plugins/examples` for working examples of each capability type.
