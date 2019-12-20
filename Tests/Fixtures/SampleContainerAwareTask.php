<?php


namespace SunValley\TaskManager\Symfony\Tests\Fixtures;


use SunValley\TaskManager\ProgressReporter;
use SunValley\TaskManager\Symfony\Task\AbstractSymfonyContainerAwareTask;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SampleContainerAwareTask extends AbstractSymfonyContainerAwareTask
{

    protected function __run(ProgressReporter $reporter, ContainerInterface $container)
    {
        $options = $this->getOptions();
        
        return 'Result: ' . $options['data'];
    }

    function buildOptionsResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired('data');

        return $resolver;
    }
}