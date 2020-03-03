<?php


namespace ReallyOrm\Test\Hydrator;

use ReallyOrm\Entity\EntityInterface;
use ReallyOrm\Hydrator\HydratorInterface;
use ReflectionClass;
use ReflectionException;

class Hydrator implements HydratorInterface
{

    public function getAttributes(){

    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function hydrate(string $className, array $data): EntityInterface
    {
        $reflixionClass = new ReflectionClass($className);
        $entityClass = new $className;
        foreach ($reflixionClass->getProperties() as $property) {
            if(!preg_match('/@ORM (\w+) ?/', $property->getDocComment(), $propertyName)){
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
        $reflixionClass = new ReflectionClass(get_class($entity));
        $array = array();
        foreach ($reflixionClass->getProperties() as $property) {
            if(!preg_match('/@ORM (\w+) ?/', $property->getDocComment(), $propertyName)){
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
     */
    public function hydrateId(EntityInterface $entity, int $id): void
    {
        // TODO: Implement hydrateId() method.
    }
}
