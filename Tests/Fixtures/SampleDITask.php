<?php


namespace SunValley\TaskManager\Symfony\Tests\Fixtures;


use SunValley\TaskManager\ProgressReporter;
use SunValley\TaskManager\Symfony\Task\AbstractSymfonyDITask;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SampleDITask extends AbstractSymfonyDITask
{

    private $injectedString = "original";

    /**
     * @param string $injectedString
     */
    public function setInjectedString(string $injectedString): void
    {
        $this->injectedString = $injectedString;
    }


    protected function __run(ProgressReporter $progressReporter)
    {
        return $this->injectedString;
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