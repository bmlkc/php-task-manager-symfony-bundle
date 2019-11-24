<?php


namespace SunValley\TaskManager\Symfony\Task;


use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

class TaskEnvironment implements TaskEnvironmentInterface
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

    /** @inheritDoc */
    public function register()
    {
        $kernel = $this->container->get('kernel');

        putenv('PTM_SB_KERNEL_CLASS=' . get_class($kernel));
        putenv('PTM_SB_KERNEL_ARGS=' . base64_encode(serialize($this->getKernelArguments())));
    }

    /**
     * Returns arguments passed to the Kernel
     *
     * @return array
     */
    protected function getKernelArguments(): array
    {
        $kernel = $this->container->get('kernel');

        return [
            $kernel->getEnvironment(),
            $kernel->isDebug(),
        ];
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

        $kernelArgs = $_ENV['PTM_SB_KERNEL_ARGS'] ?? $_SERVER['PTM_SB_KERNEL_ARGS']?? null;
        $kernelArgs = unserialize(base64_decode($kernelArgs));
        if ($kernelArgs === null) {
            throw new \RuntimeException('Unable to find required environmental variables');
        }

        $r      = new \ReflectionClass($kernelClass);
        /** @var Kernel $kernel */
        $kernel = $r->newInstanceArgs($kernelArgs);
        
        return $kernel;
    }
}