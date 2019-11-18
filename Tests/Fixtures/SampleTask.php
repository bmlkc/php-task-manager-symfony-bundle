<?php


namespace SunValley\TaskManager\Symfony\Tests\Fixtures;


use SunValley\TaskManager\ProgressReporter;
use SunValley\TaskManager\Symfony\Task\AbstractSymfonyTask;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SampleTask extends AbstractSymfonyTask
{

    protected function __run(ProgressReporter $reporter, ContainerInterface $container): void
    {
        $options = $this->getOptions();
        $reporter->finishTask('Result: ' . $options['data']);
    }

    function buildOptionsResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired('data');

        return $resolver;
    }
}