<?php

namespace ReallyOrm\Repository;

use PDO;
use ReallyOrm\Entity\EntityInterface;
use ReallyOrm\Hydrator\HydratorInterface;
use ReallyOrm\Test\Entity\User;
use ReflectionClass;

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
        return $this->entityName;
    }

    public function getTableName()
    {
        $clasName = explode("\\", $this->entityName);
        return strtolower($clasName[sizeof($clasName) - 1]);
    }

    /**
     * @inheritDoc
     */
    public function find(int $id): ?EntityInterface
    {
        $query = "SELECT * FROM " . $this->getTableName() . " WHERE id=:id;";
        $dbStmt = $this->pdo->prepare($query);
        $dbStmt->bindParam(':id', $id);
        $dbStmt->execute();
        $row = $dbStmt->fetch();

        return $this->hydrator->hydrate($this->entityName, $row);
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

        return substr($allFilters, "0", "-5");
    }

    /**
     * @inheritDoc
     */
    public function findOneBy(array $filters): ?EntityInterface
    {
        $query = "SELECT * FROM " . $this->getTableName() . $this->getFilters($filters) . " LIMIT 1;";
        $dbStmt = $this->pdo->prepare($query);
        foreach ($filters as $fieldName => &$value) {
            $dbStmt->bindParam(':' . $fieldName, $value);
        }
        $dbStmt->execute();
        $row = $dbStmt->fetch();
        return $this->hydrator->hydrate($this->entityName, $row);
    }

    private function getSorts(array $sorts)
    {
        $allSorts = '';
        if (sizeof($sorts) > 0) {
            $allSorts .= ' ORDER BY ';
        }
        foreach ($sorts as $fieldName => $direction) {
            if ($direction === "DESC") {
                $allSorts .= $fieldName . ' DESC, ';
                continue;
            }
            $allSorts .= $fieldName . ' ASC, ';
        }
        return substr($allSorts, "0", "-2");
    }

    /**
     * @inheritDoc
     */
    public function findBy(array $filters, array $sorts, int $from, int $size): array
    {
        $query = "SELECT * FROM " .
            $this->getTableName() .
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

    private function getColumns(EntityInterface $entity, array $data)
    {
        $columns = '';
        $data = $this->hydrator->extract($entity);
        foreach ($data as $fieldName => $value) {
            $columns .= $fieldName . ', ';
        }

        return substr($columns, '0', '-2');
    }

    private function getValues(EntityInterface $entity, array $data)
    {
        $columns = '';
        $data = $this->hydrator->extract($entity);
        foreach ($data as $fieldName => $value) {
            $columns .= ':' . $fieldName . ', ';
        }

        return substr($columns, '0', '-2');
    }

    private function getUpdatedValues(EntityInterface $entity, array $data)
    {
        $columns = '';
        foreach ($data as $fieldName => $value) {
            if ($fieldName === 'id') {
                continue;
            }
            $columns .= $fieldName . ' = VALUES(' . $fieldName . '), ';
        }

        return substr($columns, '0', '-2');
    }

    /**
     * @inheritDoc
     */
    public function insertOnDuplicateKeyUpdate(EntityInterface $entity): bool
    {
        $data = $this->hydrator->extract($entity);
        $query = 'INSERT INTO ' . $this->getTableName() . ' (' . $this->getColumns($entity, $data) . ')
                    VALUES (' . $this->getValues($entity, $data) . ') ON DUPLICATE KEY UPDATE 
                    ' . $this->getUpdatedValues($entity, $data) . ';';
        $dbStmt = $this->pdo->prepare($query);
        foreach ($data as $fieldName => &$value) {
            $f = $value;
            if (is_array($f)) {
                $result = implode(",", $f);
                $dbStmt->bindValue(':' . $fieldName, $result);
                continue;
            }
            $dbStmt->bindParam(':' . $fieldName, $value);
        }

        return $dbStmt->execute();
    }

    /**
     * @inheritDoc
     */
    public function delete(EntityInterface $entity): bool
    {
        $query = 'DELETE FROM ' . $this->getTableName() . ' WHERE id=:id';
        $dbStmt = $this->pdo->prepare($query);
        $id = $entity->getId();
        $dbStmt->bindParam(':id', $id);
        $dbStmt->execute();
        return $dbStmt->rowCount() > 0;
    }

    /**
     * @param EntityInterface $entity
     * @param EntityInterface $target
     * @return bool
     */
//    public function setForeignKeyId(EntityInterface $entity, EntityInterface $target)
//    {
//        $entityId = $entity->getId();
//
//        $data = $this->hydrator->extract($target);
//        $query = 'INSERT INTO ' . $this->getTableName() . ' (' . $this->getColumns($entity, $data) . ')
//                    VALUES (' . $this->getValues($entity, $data) . ') ON DUPLICATE KEY UPDATE
//                    ' . $this->getUpdatedValues($entity, $data) . ';';
//        $dbStmt = $this->pdo->prepare($query);
//        foreach ($data as $fieldName => &$value) {
//            $dbStmt->bindParam(':' . $fieldName, $value);
//        }
//
//        return $dbStmt->execute();
//    }

}
