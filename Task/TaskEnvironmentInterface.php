<?php

namespace SunValley\TaskManager\Symfony\Task;

interface TaskEnvironmentInterface
{
    /**
     * Registers environmental variables to pass required Kernel parameters to child
     */
    public function register();
}