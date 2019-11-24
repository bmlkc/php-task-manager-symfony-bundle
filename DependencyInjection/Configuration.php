<?php

namespace SunValley\TaskManager\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validation;

class Configuration implements ConfigurationInterface
{

    /** @inheritDoc */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('php_task_manager');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('php_task_manager');
        }

        $this->buildUrlConfiguration($rootNode);
        $this->buildPoolConfiguration($rootNode);

        return $treeBuilder;
    }

    public function buildUrlConfiguration(ArrayNodeDefinition $node)
    {
        // @formatter:off
        $node->children()
                 ->scalarNode('task_queue')
                     ->isRequired()
                     ->validate()
                         ->ifTrue($this->urlValidatorClosure())
                         ->thenInvalid('Given task queue does not represent a valid URL!')
                     ->end()
                 ->end()
                 ->scalarNode('task_storage')
                    ->validate()
                    ->ifTrue($this->urlValidatorClosure())
                    ->thenInvalid('Given task storage does not represent a valid URL!')
                 ->end()
            ->end()
        ->end();
        
        // @formatter:on
    }

    protected function urlValidatorClosure()
    {
        return \Closure::fromCallable(
            function ($val) {
                $constraint = new Url(
                    [
                        'protocols' => ['redis', 'rediss'],
                    ]
                );

                $validator  = Validation::createValidator();
                $violations = $validator->validate($val, $constraint);

                return 0 !== count($violations);
            }
        );
    }

    public function buildPoolConfiguration(ArrayNodeDefinition $node)
    {
        // @formatter:off
        $node->children()
                ->arrayNode('pool')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('minimum_processes')
                            ->defaultValue(1)
                            ->validate()
                                ->ifTrue($this->integerRangeCheckClosure(1, null))
                                ->thenInvalid('Minimum processes can be minimum 1')
                            ->end()
                        ->end()
                        ->integerNode('maximum_processes')
                            ->defaultValue(10)
                            ->validate()
                                ->ifTrue($this->integerRangeCheckClosure(1, null))
                                ->thenInvalid('Maximum processes can be minimum 1')
                            ->end()
                        ->end()
                        ->integerNode('time_to_live')
                            ->defaultValue(60)
                            ->validate()
                                ->ifTrue($this->integerRangeCheckClosure(0, null))
                                ->thenInvalid('Time to live for a worker after completing a job should be bigger than or equal to 0')
                            ->end()
                        ->end()
                        ->integerNode('max_jobs_per_process')
                            ->defaultValue(10)
                            ->validate()
                                ->ifTrue($this->integerRangeCheckClosure(1, null))
                                ->thenInvalid('Maximum jobs per process should be more than 0')
                            ->end()
                        ->end()
                ->end()
                    ->validate()
                        ->ifTrue(function($values) {
                            return $values['minimum_processes'] > $values['maximum_processes'];
                        })
                        ->thenInvalid('Maximum processes cannot be smaller than minimum processes')
                    ->end()
            ->end()
        ->end()
        ;    
        // @formatter:on
    }

    protected function integerRangeCheckClosure(?int $min, ?int $max)
    {
        return \Closure::fromCallable(
            function ($val) use ($min, $max) {
                $constraint = new Range(
                    [
                        'min' => $min,
                        'max' => $max,
                    ]
                );

                $validator  = Validation::createValidator();
                $violations = $validator->validate($val, $constraint);

                return 0 !== count($violations);
            }
        );
    }
}