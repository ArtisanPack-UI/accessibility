<?php

$root = dirname(__DIR__);

return [
    'enabled' => true,
    'safe_mode' => false,
    // Directories to scan for conventional plugins (each directory contains subdirectories with plugin.json)
    'paths' => [
        $root . '/plugins',
        $root . '/plugins/examples',
    ],
    // Optional allow/deny lists by plugin id
    'allowlist' => [],
    'denylist' => [],
];
