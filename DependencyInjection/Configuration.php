<?php

namespace SunValley\TaskManager\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /** @inheritDoc */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('php_task_manager');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('php_task_manager');
        }

        // TODO:  complete configuration file parsing
        
        return $treeBuilder;
    }
}