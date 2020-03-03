<?php


namespace ReallyOrm\Test\Repository;


use PDO;
use ReallyOrm\Entity\EntityInterface;
use ReallyOrm\Repository\RepositoryInterface;
use ReallyOrm\Test\Entity\User;

class UserRepository implements RepositoryInterface
{

    private $pdo;
    private $className;
    private $hydrator;

    public function __construct($pdo, $className, $hydrator)
    {
        $this->pdo = $pdo;
        $this->className = $className;
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritDoc
     */
    public function find(int $id): ?EntityInterface
    {
        $query = "SELECT * FROM user WHERE id=:id";
        $dbStmt = $this->pdo->prepare($query);
        $dbStmt->bindParam(':')
        $row = $dbStmt->fetch();
        $product = $this->hydrator->hydrate(User::class, $row);

        return $product;

    }

    /**
     * @inheritDoc
     */
    public function findOneBy(array $filters): ?EntityInterface
    {
        // TODO: Implement findOneBy() method.
    }

    /**
     * @inheritDoc
     */
    public function findBy(array $filters, array $sorts, int $from, int $size): array
    {
        // TODO: Implement findBy() method.
    }

    /**
     * @inheritDoc
     */
    public function insertOnDuplicateKeyUpdate(EntityInterface $entity): bool
    {
        $data = $hydrator->extract($product); // results in something like ['id' => 1, 'name' => 'Product ABC']

        // prepare statement and execute it. execution result will be a boolean.

        $this->hydrator->hydrateId($product, $this->pdo->lastInsertId());

        return $result;

    }

    /**
     * @inheritDoc
     */
    public function delete(EntityInterface $entity): bool
    {
        // TODO: Implement delete() method.
    }

    /**
     * @inheritDoc
     */
    public function getEntityName(): string
    {
        // TODO: Implement getEntityName() method.
    }
}