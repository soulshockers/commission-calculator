<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Dotenv\Dotenv;

class Kernel
{
    private ContainerBuilder $containerBuilder;

    public function __construct()
    {
        $this->containerBuilder = new ContainerBuilder();
    }

    public function boot(): void
    {
        $this->loadEnvironmentVariables();
        $this->loadContainerConfiguration();
        $this->containerBuilder->compile(resolveEnvPlaceholders: true);
    }

    public function getContainer(): ContainerBuilder
    {
        return $this->containerBuilder;
    }

    private function loadEnvironmentVariables(): void
    {
        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__ . '/../.env');
    }

    private function loadContainerConfiguration(): void
    {
        $loader = new YamlFileLoader($this->containerBuilder, new FileLocator(__DIR__ . '/../config'));
        $loader->load('services.yaml');
    }
}
