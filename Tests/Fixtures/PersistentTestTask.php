<?php


namespace SunValley\TaskManager\Symfony\Tests\Fixtures;

use SunValley\TaskManager\ProgressReporter;
use SunValley\TaskManager\Symfony\Task\AbstractSymfonyContainerAwareTask;
use SunValley\TaskManager\Symfony\Task\PersistentTaskTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersistentTestTask extends AbstractSymfonyContainerAwareTask
{
    use PersistentTaskTrait;

    protected function __run(ProgressReporter $progressReporter, ContainerInterface $container)
    {
        return 'hello';
    }

    /**
     * Return options resolver to limit arguments
     *
     * @return OptionsResolver
     */
    function buildOptionsResolver(): OptionsResolver
    {
        return new OptionsResolver();
    }
}