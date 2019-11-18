<?php


namespace SunValley\TaskManager\Symfony\Tests;

use SunValley\TaskManager\Symfony\PhpTaskManagerBundle;
use SunValley\TaskManager\TaskQueue\InMemoryTaskQueue;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{

    /** @var array */
    private $extensionConfigs = [];

    /** @var string */
    private $containerClass;

    public function registerBundles()
    {
        return [
            new PhpTaskManagerBundle(),
        ];
    }

    /**
     * @param array $extensionConfigs
     */
    public function setExtensionConfigs(array $extensionConfigs): void
    {
        $this->extensionConfigs = $extensionConfigs;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }

    protected function build(ContainerBuilder $container)
    {
        parent::build($container);

        $envConfig = $_ENV['PTM_TEST_KERNEL_CONFIG'] ?? $_SERVER['PTM_TEST_KERNEL_CONFIG'] ?? null;
        if ($envConfig) {
            $envConfig              = unserialize(base64_decode($envConfig));
            $this->extensionConfigs = $envConfig;
        }

        foreach ($this->extensionConfigs as $name => $config) {
            $container->prependExtensionConfig($name, $config);
        }

        $container->addCompilerPass(
            new class implements CompilerPassInterface
            {

                public function process(ContainerBuilder $container)
                {
                    $container->getDefinition('SunValley\TaskManager\Configuration')->setPublic(true);
                    $container->getDefinition('SunValley\TaskManager\Symfony\Task\TaskManagerFactory')
                              ->setArgument(
                                  1,
                                  new Definition(
                                      InMemoryTaskQueue::class,
                                      [$container->getDefinition('php_task_manager_loop')]
                                  )
                              );
                }
            }
        );
    }

    protected function getContainerClass()
    {
        // prevents cache

        if ($this->containerClass !== null) {
            return $this->containerClass;
        }

        return $this->containerClass = parent::getContainerClass() . uniqid();
    }

}