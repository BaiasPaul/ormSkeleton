<?php

namespace ReallyOrm\Repository;

use PDO;
use ReallyOrm\Entity\EntityInterface;
use ReallyOrm\Hydrator\HydratorInterface;
use ReallyOrm\Test\Entity\User;

/**
 * Class AbstractRepository.
 *
 * Intended as a parent for entity repositories.
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * Represents a connection between PHP and a database server.
     *
     * https://www.php.net/manual/en/class.pdo.php
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * The name of the entity associated with the repository.
     *
     * This could be used, for example, to infer the underlying table name.
     *
     * @var string
     */
    protected $entityName;

    /**
     * The hydrator is used in the following two cases:
     * - build an entity from a database row
     * - extract entity fields into an array representation that is easier to use when building insert/update statements.
     *
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * AbstractRepository constructor.
     *
     * @param \PDO $pdo
     * @param string $entityName
     * @param HydratorInterface $hydrator
     */
    public function __construct(PDO $pdo, string $entityName, HydratorInterface $hydrator)
    {
        $this->pdo = $pdo;
        $this->entityName = $entityName;
        $this->hydrator = $hydrator;
    }

    /**
     * Returns the name of the associated entity.
     *
     * @return string
     */
    public function getEntityName(): string
    {
        $clasName = explode("\\", $this->entityName);
        return strtolower($clasName[sizeof($clasName) - 1]);
    }

    /**
     * @inheritDoc
     */
    public function find(int $id): ?EntityInterface
    {
        $query = "SELECT * FROM " . $this->getEntityName() . " WHERE id=:id;";
        $dbStmt = $this->pdo->prepare($query);
        $dbStmt->bindParam(':id', $id);
        $dbStmt->execute();
        $row = $dbStmt->fetch();
        $result = $this->hydrator->hydrate($this->entityName, $row);

        return $result;
    }

    private function getFilters(array $filters)
    {
        $allFilters = '';
        if (sizeof($filters) > 0) {
            $allFilters .= ' WHERE ';
        }
        foreach ($filters as $fieldName => $value) {
            $allFilters .= $fieldName . '=:' . $fieldName . ' AND ';
        }
        $allFilters = substr($allFilters, "0", "-5");

        return $allFilters;
    }

    /**
     * @inheritDoc
     */
    public function findOneBy(array $filters): ?EntityInterface
    {
        $query = "SELECT * FROM " . $this->getEntityName() . $this->getFilters($filters) . " LIMIT 1;";
        $dbStmt = $this->pdo->prepare($query);
        foreach ($filters as $fieldName => &$value) {
            $dbStmt->bindParam(':' . $fieldName, $value);
        }
        $dbStmt->execute();
        $row = $dbStmt->fetch();
        $result = $this->hydrator->hydrate($this->entityName, $row);

        return $result;
    }

    private function getSorts(array $sorts)
    {
        $allSorts = '';
        if (sizeof($sorts) > 0) {
            $allSorts .= ' ORDER BY ';
        }
        foreach ($sorts as $fieldName => $direction) {
            if($direction === "DESC"){
                $allSorts .= $fieldName . ' DESC, ';
                continue;
            }
            $allSorts .= $fieldName . ' ASC, ';
        }
        $allSorts = substr($allSorts, "0", "-2");

        return $allSorts;
    }

    /**
     * @inheritDoc
     */
    public function findBy(array $filters, array $sorts, int $from, int $size): array
    {
        $query = "SELECT * FROM " .
            $this->getEntityName() .
            $this->getFilters($filters) .
            $this->getSorts($sorts) .
            " LIMIT :limit OFFSET :offset;";
        $dbStmt = $this->pdo->prepare($query);
        $dbStmt->bindParam(':limit', $size);
        $dbStmt->bindParam(':offset', $from);
        foreach ($filters as $fieldName => &$value) {
            $dbStmt->bindParam(':' . $fieldName, $value);
        }
        $dbStmt->execute();
        $rows = $dbStmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->hydrator->hydrate($this->entityName, $row);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function insertOnDuplicateKeyUpdate(EntityInterface $entity): bool
    {
        $query = "SET TABLE " . $this->getEntityName() . ";";
        if ($entity->getId() == null){
            $query = "INSERT INTO " . $this->getEntityName() . ";";
        }
        $find = $this->find($entity->getId());


        $dbStmt = $this->pdo->prepare($query);

        return $dbStmt->execute();
    }

    /**
     * @inheritDoc
     */
    public function delete(EntityInterface $entity): bool
    {
        // TODO: Implement delete() method.
    }
}
