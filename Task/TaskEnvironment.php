<?php


namespace SunValley\TaskManager\Symfony\Task;


use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

class TaskEnvironment
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * TaskEnvironment constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Registers environmental variables to pass required Kernel parameters to child
     */
    public function register()
    {
        $kernel = $this->container->get('kernel');

        putenv('PTM_SB_KERNEL_CLASS=' . get_class($kernel));
        putenv('PTM_SB_KERNEL_ENV=' . $kernel->getEnvironment());
        putenv('PTM_SB_KERNEL_DEBUG=' . $kernel->isDebug());
    }

    /**
     * Generates a new Kernel from registered env vars
     *
     * @return Kernel
     */
    public static function generateKernelFromEnv(): Kernel
    {
        $kernelClass = $_ENV['PTM_SB_KERNEL_CLASS'] ?? $_SERVER['PTM_SB_KERNEL_CLASS'];
        if (empty($kernelClass)) {
            throw new \RuntimeException('Unable to find required environmental variable PTM_SB_KERNEL_CLASS');
        }

        if (!is_a($kernelClass, Kernel::class, true)) {
            throw new \RuntimeException('Given class is not a Symfony Kernel class');
        }

        $kernelDebug = $_ENV['PTM_SB_KERNEL_DEBUG'] ?? $_SERVER['PTM_SB_KERNEL_DEBUG'];
        $kernelEnv   = $_ENV['PTM_SB_KERNEL_ENV'] ?? $_SERVER['PTM_SB_KERNEL_ENV'];
        if (!isset($kernelDebug) || !isset($kernelEnv)) {
            throw new \RuntimeException('Unable to find required environmental variables');
        }

        return new $kernelClass($kernelEnv, $kernelDebug);
    }
}