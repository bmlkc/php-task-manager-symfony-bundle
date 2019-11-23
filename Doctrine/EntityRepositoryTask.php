<?php

namespace SunValley\TaskManager\Symfony\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use SunValley\TaskManager\ProgressReporter;
use SunValley\TaskManager\Symfony\Task\AbstractPersistentSymfonyTask as BaseTask;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EntityRepositoryTask defines a task that can be used to access an EntityRepository.
 *
 * Usage - find:
 * <code>
 * $task = new EntityRepositoryTask(uniqid(), [
 *      'entity' => Entity::class,
 *      'method' => 'find',
 *      'arguments' => ['id'=>1312],
 * ]);
 *  // or
 * $task = EntityRepositoryTask::find(uniqid(),Entity::class, 1312);
 * </code>
 *
 * Usage - findAll:
 * <code>
 * new EntityRepositoryTask(uniqid(), [
 *      'entity' => Entity::class,
 *      'method' => 'findAll',
 * ]);
 *  // or
 * $task = EntityRepositoryTask::findAll(uniqid(),Entity::class);
 * </code>
 *
 * Usage - findOneBy:
 * <code>
 * new EntityRepositoryTask(uniqid(), [
 *      'entity' => Entity::class,
 *      'method' => 'findOneBy',
 *      'arguments' => [
 *          'field' => 'value',
 *      ],
 * ]);
 *  // or
 * $task = EntityRepositoryTask::findOneBy(uniqid(),Entity::class,['field' => 'value']);
 * </code>
 *
 * Usage - findBy:
 * <code>
 * new EntityRepositoryTask(uniqid(), [
 *      'entity' => Entity::class,
 *      'method' => 'findBy',
 *      'arguments' => [
 *          'criteria' => ['field' => 'value'],
 *          'orderBy' => ['field' => 'DESC']. #optional
 *          'limit' => 10, #optional
 *          'offset'=> 10, #optional
 *      ],
 * ]);
 *  // or
 * $task = EntityRepositoryTask::findOneBy(uniqid(),Entity::class,['field' => 'value'],['field' => 'DESC'],10,10);
 * </code>
 *
 * There is also an additional option to change the entity manager service seeked in the container.
 * This defaults to `entity_manager`.
 *
 * The `fresh` option, which defaults to TRUE, makes sure object manager returns a fresh instance of the entity.
 *
 * @package SunValley\TaskManager\Symfony\Doctrine
 */
class EntityRepositoryTask extends BaseTask
{

    /** @inheritDoc */
    protected function __run(ProgressReporter $reporter, ContainerInterface $container)
    {
        $options = $this->getOptions();
        /** @var ObjectManager $manager */
        $manager = $container->get($options['entity_manager_service']);

        $entity    = $options['entity'];
        $method    = $options['method'];
        $arguments = $options['arguments'];
        $fresh     = $options['fresh'];

        if ($method === 'find') {
            $result = $manager->find($entity, $arguments['id']);
        } elseif ($method === 'findOneBy') {
            $result = $manager->getRepository($entity)->findOneBy($arguments);
        } elseif ($method === 'findBy') {
            $criteria = $arguments['criteria'];
            $orderBy  = $arguments['orderBy'];
            $limit    = $arguments['limit'];
            $offset   = $arguments['offset'];

            $result = $manager->getRepository($entity)->findBy($criteria, $orderBy, $limit, $offset);
        } elseif ($method === 'findAll') {
            $result = $manager->getRepository($entity)->findAll();
        } else {
            $result = null;
        }

        if ($fresh && $result) {
            if (is_array($result)) {
                array_map([$manager, 'refresh'], $result);
            } else {
                $manager->refresh($result);
            }
        }

        return $result;
    }

    /** @inheritDoc */
    function buildOptionsResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        // If manager should return fresh entity
        $resolver->setDefault('fresh', true);
        $resolver->setAllowedTypes('fresh', 'bool');
        // Name of the entity manager should be asked
        $resolver->setRequired('entity');
        $resolver->setAllowedTypes('entity', 'string');
        // Entity manager service that the container will be asked
        $resolver->setDefault('entity_manager_service', 'entity_manager');
        $resolver->setAllowedTypes('entity_manager_service', 'string');
        // Method call to entity repository
        $resolver->setRequired('method');
        $resolver->setAllowedValues('method', ['find', 'findAll', 'findBy', 'findOneBy']);
        // Arguments that will be sent with method. See class description.
        $resolver->setDefault('arguments', []);
        $resolver->setAllowedTypes('arguments', 'array');
        $resolver->setNormalizer(
            'arguments',
            function (Options $options, $value) {
                $method = $options['method'];
                if ($method === 'find' && !array_key_exists('id', $value)) {
                    throw new \InvalidArgumentException('"find" method requires an id as an argument');
                } elseif ($method === 'findAll') {
                    $value = [];
                } elseif ($method === 'findOneBy' && empty($value)) {
                    throw new \InvalidArgumentException('"findOneBy" method requires criteria');
                } elseif ($method === 'findBy' && empty($value['criteria'])) {
                    throw new \InvalidArgumentException('"findBy" method requires criteria');
                }

                return $value;
            }
        );

        return $resolver;
    }

    /**
     * Generates a find task
     *
     * @param string $taskId
     * @param string $entity
     * @param string $entityId
     *
     * @return static
     * @throws \SunValley\TaskManager\Exception\TaskOptionValidationException
     */
    public static function find(string $taskId, string $entity, string $entityId): self
    {
        return new static(
            $taskId,
            [
                'entity'    => $entity,
                'method'    => 'find',
                'arguments' => ['id' => $entityId],
            ]
        );
    }

    /**
     * Generates a findAll task
     *
     * @param string $taskId
     * @param string $entity
     *
     * @return static
     * @throws \SunValley\TaskManager\Exception\TaskOptionValidationException
     */
    public static function findAll(string $taskId, string $entity): self
    {
        return new static(
            $taskId,
            [
                'entity' => $entity,
                'method' => 'findAll',
            ]
        );
    }

    /**
     * Generates a find task
     *
     * @param string $taskId
     * @param string $entity
     * @param array  $criteria
     *
     * @return static
     * @throws \SunValley\TaskManager\Exception\TaskOptionValidationException
     */
    public static function findOneBy(string $taskId, string $entity, array $criteria): self
    {
        return new static(
            $taskId,
            [
                'entity'    => $entity,
                'method'    => 'find',
                'arguments' => $criteria,
            ]
        );
    }

    /**
     * Generates a find task
     *
     * @param string     $taskId
     * @param string     $entity
     * @param array      $criteria
     *
     * @param array|null $orderBy
     * @param null       $limit
     * @param null       $offset
     *
     * @return static
     * @throws \SunValley\TaskManager\Exception\TaskOptionValidationException
     */
    public static function findBy(
        string $taskId,
        string $entity,
        array $criteria,
        ?array $orderBy = null,
        $limit = null,
        $offset = null
    ): self {
        return new static(
            $taskId,
            [
                'entity'    => $entity,
                'method'    => 'find',
                'arguments' => [
                    'criteria' => $criteria,
                    'orderBy'  => $orderBy,
                    'limit'    => $limit,
                    'offset'   => $offset,
                ],
            ]
        );
    }
}