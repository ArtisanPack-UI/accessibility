<?php

namespace ArtisanPack\Accessibility\Plugins\Contracts;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Context
{
    public function __construct(
        private array $config = [],
        private ?LoggerInterface $logger = null,
        private ?ContainerInterface $container = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function getConfig(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }
        return $this->config[$key] ?? $default;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }
}
