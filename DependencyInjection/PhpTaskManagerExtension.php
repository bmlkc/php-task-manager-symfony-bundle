<?php

namespace SunValley\TaskManager\Symfony\DependencyInjection;

use SunValley\TaskManager\TaskQueue\RedisTaskQueue;
use SunValley\TaskManager\TaskStorage\RedisTaskStorage;
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
        $loader->load('services.yaml');

        $loopDefinition    = $container->getDefinition('php_task_manager_loop');
        $storageDefinition = $container->getDefinition('php_task_manager_storage');
        if (!empty($config['task_storage'])) {
            $storageProtocol = parse_url($config['task_storage'], PHP_URL_SCHEME);
            if (stripos($storageProtocol, 'redis') === 0) {
                $storageDefinition->setClass(RedisTaskStorage::class);
                $storageDefinition->setArguments([$loopDefinition, $config['task_storage']]);
            } else {
                throw new \RuntimeException(sprintf('Storage protocol %s is not supported', $storageProtocol));
            }
        } else {
            $storageDefinition = null;
            $container->removeDefinition('php_task_manager_storage');
        }

        $queueProtocol = parse_url($config['task_queue'], PHP_URL_SCHEME);
        if (stripos($queueProtocol, 'redis') === 0) {
            $queueDefinition = $container->getDefinition('SunValley\TaskManager\TaskQueueInterface');
            $queueDefinition->setClass(RedisTaskQueue::class);
            $queueDefinition->setArguments([$config['task_queue'], $loopDefinition, $storageDefinition]);
        } else {
            throw new \RuntimeException(sprintf('Queue protocol %s is not supported', $queueProtocol));
        }

        $configDefinition = $container->getDefinition('SunValley\TaskManager\Configuration');
        if (isset($config['pool']['maximum_processes'])) {
            $configDefinition->addMethodCall('setMaxProcesses', [$config['pool']['maximum_processes']]);
        }

        if (isset($config['pool']['minimum_processes'])) {
            $configDefinition->addMethodCall('setMinProcesses', [$config['pool']['minimum_processes']]);
        }

        if (isset($config['pool']['time_to_live'])) {
            $configDefinition->addMethodCall('setTtl', [$config['pool']['time_to_live']]);
        }

        if (isset($config['pool']['max_jobs_per_process'])) {
            $configDefinition->addMethodCall('setMaxJobsPerProcess', [$config['pool']['max_jobs_per_process']]);
        }

    }
    
}