<?php


namespace SunValley\TaskManager\Symfony\Tests\Fixtures;


use SunValley\TaskManager\Symfony\Task\AbstractSymfonyDITask;
use SunValley\TaskManager\Symfony\Task\DependencyInjectorInterface;

class SampleDITaskDependencyInjector implements DependencyInjectorInterface
{
    /**
     * @param AbstractSymfonyDITask $task
     *
     * @throws \Exception
     */
    public function injectDependencies(AbstractSymfonyDITask $task)
    {
        if (!$task instanceof SampleDITask) {
            throw new \Exception('unexpected class of task');
        }

        /** @var SampleDITask $task */
        $task->setInjectedString('injected');
    }
}