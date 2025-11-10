<?php

use ArtisanPack\Accessibility\Plugins\Contracts\Context;
use ArtisanPack\Accessibility\Plugins\PluginManager;

it('discovers example plugins from conventional directory and registers capabilities', function () {
    $root = dirname(__DIR__, 3);
    $config = [
        'plugins' => [
            'enabled' => true,
            'safe_mode' => false,
            'paths' => [
                $root . '/plugins/examples',
            ],
        ],
    ];

    $manager = new PluginManager(new Context($config));
    $manager->discoverAndRegister();

    // Color format plugin available
    $hex = $manager->getColorFormatPluginFor('hex');
    expect($hex)->not->toBeNull();
    expect($hex->parse('#abc'))->toBe('#aabbcc');

    // Rule plugin available and returns violation for low contrast
    $rules = $manager->getRulePlugins();
    expect($rules)->not->toBeEmpty();
    $rule = $rules[0];
    $results = $rule->evaluate([
        'foreground' => '#000000',
        'background' => '#000001',
        'level' => 'AA',
    ], new Context());
    expect($results->all())->not->toBe([]);

    // Analysis tool present and returns report
    $analysis = $manager->getAnalysisPlugins();
    expect($analysis)->not->toBeEmpty();
    $report = $analysis[0]->analyze([
        'links' => [
            ['href' => '#', 'text' => ''],
            ['href' => '#', 'text' => 'Read more'],
        ],
    ], new Context());
    expect($report->title)->toBe('Links Analysis');
    expect($report->data['total'])->toBe(2);
    expect($report->data['missing_text'])->toBe(1);
});

it('honors allowlist and denylist', function () {
    $root = dirname(__DIR__, 3);

    // Allowlist only the rule plugin
    $config = [
        'plugins' => [
            'enabled' => true,
            'paths' => [$root . '/plugins/examples'],
            'allowlist' => ['example.contrast_rule'],
        ],
    ];
    $manager = new PluginManager(new Context($config));
    $manager->discoverAndRegister();
    expect($manager->getColorFormatPluginFor('hex'))->toBeNull();
    expect($manager->getRulePlugins())->not->toBeEmpty();

    // Denylist the rule plugin
    $config['plugins']['allowlist'] = [];
    $config['plugins']['denylist'] = ['example.contrast_rule'];
    $manager = new PluginManager(new Context($config));
    $manager->discoverAndRegister();

    $rulePlugins = $manager->getRulePlugins();
    $ids = [];
    foreach ($manager->getPlugins() as $id => $_p) { $ids[] = $id; }
    expect(in_array('example.contrast_rule', $ids, true))->toBeFalse();
});

it('supports safe_mode to prevent activation', function () {
    $root = dirname(__DIR__, 3);
    $config = [
        'plugins' => [
            'enabled' => true,
            'safe_mode' => true,
            'paths' => [$root . '/plugins/examples'],
        ],
    ];

    $manager = new PluginManager(new Context($config));
    $manager->discoverAndRegister();

    // Plugins should be discovered even if not activated
    expect($manager->getPlugins())->not->toBeEmpty();

    // Should be safe to stop/destroy with no activation
    $manager->stopAndDestroyAll();
    expect(true)->toBeTrue();
});
