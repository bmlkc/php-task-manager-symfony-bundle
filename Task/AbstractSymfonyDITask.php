<?php


namespace SunValley\TaskManager\Symfony\Task;


use SunValley\TaskManager\ProgressReporter;
use Symfony\Component\HttpKernel\Kernel;

abstract class AbstractSymfonyDITask extends AbstractSymfonyTask
{

    const INJECTOR_SERVICE_ID_SUFFIX = "DependencyInjector";



    protected function runWithInitializedKernel(ProgressReporter $progressReporter, Kernel $kernel)
    {
        // let us try to find if there is an "injector" service for these kind of tasks registered in
        // the container. The convention is that its service id should be the class name, and it
        // must be equal the the class name of the task at hand with "DependencyInjector" suffix.
        $injectorId = $this->getInjectorId($this);

        $container = $kernel->getContainer();

        if ($container->has($injectorId)) {
            /** @var DependencyInjectorInterface $injector */
            $injector = $container->get($injectorId);
            $injector->injectDependencies($this);
        }

        return $this->__run($progressReporter);
    }

    private function getInjectorId(AbstractSymfonyDITask $task)
    {
        return get_class($task) . self::INJECTOR_SERVICE_ID_SUFFIX;
    }

    abstract protected function __run(ProgressReporter $progressReporter);



}