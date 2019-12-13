<?php


namespace SunValley\TaskManager\Symfony\Task;


interface DependencyInjectorInterface
{
    public function injectDependencies(AbstractSymfonyTask $task);
}