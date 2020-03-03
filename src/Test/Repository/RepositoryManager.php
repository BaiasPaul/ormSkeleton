<?php


namespace ReallyOrm\Test\Repository;


use ReallyOrm\Entity\EntityInterface;
use ReallyOrm\Repository\RepositoryInterface;
use ReallyOrm\Repository\RepositoryManagerInterface;

class RepositoryManager implements RepositoryManagerInterface
{
    private $array;

    public function __construct($array)
    {
        $this->array = $array;
    }

    /**
     * @inheritDoc
     */
    public function register(EntityInterface $entity): void
    {
        // TODO: Implement register() method.
    }

    /**
     * @inheritDoc
     */
    public function getRepository(string $className): RepositoryInterface
    {
        // TODO: Implement getRepository() method.
    }

    /**
     * @inheritDoc
     */
    public function addRepository(RepositoryInterface $repository): RepositoryManagerInterface
    {
        // TODO: Implement addRepository() method.
    }
}