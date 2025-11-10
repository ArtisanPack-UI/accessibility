<?php

namespace ArtisanPack\Accessibility\Plugins\Contracts;

interface PluginInterface
{
    public function getMetadata(): PluginMetadata;

    public function initialize(Context $context): void;

    public function start(): void;

    public function stop(): void;

    public function destroy(): void;
}
