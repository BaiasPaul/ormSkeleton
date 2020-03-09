<?php


namespace ReallyOrm\Test\Hydrator;

use ReallyOrm\Entity\EntityInterface;
use ReallyOrm\Hydrator\HydratorInterface;
use ReallyOrm\Test\Entity\User;
use ReallyOrm\Test\Repository\RepositoryManager;
use ReflectionClass;
use ReflectionException;

/**
 * Class Hydrator
 * @package ReallyOrm\Test\Hydrator
 */
class Hydrator implements HydratorInterface
{
    /**
     * @var RepositoryManager
     */
    private $repoManager;

    /**
     * Hydrator constructor.
     * @param RepositoryManager $repositoryManager
     */
    public function __construct(RepositoryManager $repositoryManager)
    {
        $this->repoManager = $repositoryManager;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function hydrate(string $className, array $data): EntityInterface
    {
        $reflectionClass = new ReflectionClass($className);
        $entityClass = new $className;
        foreach ($reflectionClass->getProperties() as $property) {
            if (!preg_match('/@ORM (\w+) ?/', $property->getDocComment(), $propertyName)) {
                continue;
            }
            $property->setAccessible(true);
            $property->setValue($entityClass, $data[$propertyName[1]]);
        }

        return $entityClass;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function extract(EntityInterface $entity): array
    {
        $reflectionClass = new ReflectionClass(get_class($entity));
        $array = array();
        foreach ($reflectionClass->getProperties() as $property) {
            if (!preg_match('/@ORM (\w+) ?/', $property->getDocComment(), $propertyName)) {
                continue;
            }
            $property->setAccessible(true);
            $propertyValue = $property->getValue($entity);
            $array[$propertyName[1]] = $propertyValue;

        }
        return $array;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function hydrateId(EntityInterface $entity, int $id): void
    {
        $reflectionClass = new ReflectionClass($entity);
        foreach ($reflectionClass->getProperties() as $property) {
            if (preg_match('/@ID/', $property->getDocComment()) === 1) {
                $property->setAccessible(true);
                $property->setValue($entity, $id);
            }
        }
    }
}
