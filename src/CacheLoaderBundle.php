<?php

namespace CacheLoader\CacheLoaderBundle;


use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use CacheLoader\CacheLoaderBundle\DependencyInjection\CacheLoaderExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CacheLoaderBundle extends Bundle
{

    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new CacheLoaderExtension();
    }

}