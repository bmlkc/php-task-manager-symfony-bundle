<?php

namespace SunValley\TaskManager\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class PhpTaskManagerExtension extends Extension
{

    /** @inheritDoc */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor     = new Processor();
        $configuration = new Configuration();
        $config        = $processor->processConfiguration($configuration, $configs);
        $loader        = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        
        // TODO:  Load required configuration
    }

}