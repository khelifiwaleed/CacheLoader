<?php

namespace CacheLoader\CacheLoaderBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class CacheLoaderExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $load = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );

        $load->load('services.yaml');
    }
}