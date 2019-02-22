<?php

namespace hunomina\Orm\EntityRepository;

use hunomina\Orm\Database\QueryBuilder\QueryBuilder;
use hunomina\Orm\Database\QueryBuilder\QueryBuilderException;
use hunomina\Orm\Database\QueryBuilder\QueryBuilderFactory;
use hunomina\Orm\Database\Statement\StatementException;
use hunomina\Orm\Database\Statement\StatementFormatter;
use hunomina\Orm\Entity\Entity;
use hunomina\Orm\Entity\EntityException;
use hunomina\Orm\Entity\EntityReflexion;
use PDO;
use ReflectionProperty;

abstract class EntityRepository
{
    /** @var string $_type */
    protected $_type;
    /**
     * @var PDO
     */
    protected $_pdo;

    /**
     * @var StatementFormatter $_statement_formatter
     */
    protected $_statement_formatter;

    /** @var QueryBuilder $_query_builder */
    protected $_query_builder;

    /**
     * EntityRepository constructor.
     * @param PDO $pdo
     * @param string $type
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     */
    public function __construct(PDO $pdo, string $type = 'mysql')
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_pdo = $pdo;
        $this->_type = $type;
        $this->_statement_formatter = new StatementFormatter();
        $this->_query_builder = QueryBuilderFactory::get($this->getEntityTable(), $type);
    }

    /**
     * @param int $id
     * @param bool $load
     * @return Entity|null
     * @throws EntityException
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     * @throws StatementException
     */
    public function find(int $id, bool $load = true): ?Entity
    {
        $entityReflexion = new EntityReflexion($this->getEntityClass());
        $entityPrimaryKey = $entityReflexion->getPrimaryKey();

        $query = $this->_query_builder->select()->where('`' . $entityPrimaryKey->getName() . '` = :id')->build();

        try {
            $statement = $this->_pdo->prepare($query);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->execute();
        } catch (\PDOException $e) {
            throw new EntityRepositoryException($e->getMessage());
        }

        $results = $this->_statement_formatter->format($statement)->fetchObject($this->getEntityClass());

        if (\count($results) > 0) {
            $entity = $results[0];
            if ($load) {
                $this->load($entity);
            }
            return $entity;
        }
        return null;
    }

    /**
     * @param bool $load
     * @return array
     * @throws EntityException
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     * @throws StatementException
     */
    public function findAll(bool $load = true): array
    {
        $query = $this->_query_builder->select()->build();

        try {
            $statement = $this->_pdo->query($query);
        } catch (\PDOException $e) {
            throw new EntityRepositoryException($e->getMessage());
        }

        $results = $this->_statement_formatter->format($statement)->fetchObject($this->getEntityClass());
        if ($load) {
            foreach ($results as $entity) {
                $this->load($entity);
            }
        }
        return $results;
    }

    abstract protected function getEntityClass(): string;

    /**
     * @return string
     * @throws EntityRepositoryException
     */
    protected function getEntityTable(): string
    {
        $entityClass = $this->getEntityClass();
        if (class_exists($entityClass)) {
            if (is_subclass_of($entityClass, Entity::class)) {
                /** @var Entity $entityClass */
                return $entityClass::getTable();
            }
            throw new EntityRepositoryException('The Entity Repository can only use Entity classes');
        }
        throw new EntityRepositoryException('The `' . $entityClass . '` class does not exist');
    }

    /**
     * @param Entity $entity
     * @throws EntityException
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     * @throws StatementException
     */
    final protected function load(Entity $entity): void
    {
        $this->loadForeignKeys($entity);
        $this->loadCollections($entity);
    }

    /**
     * @param Entity $entity
     * @throws EntityException
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     * @throws StatementException
     */
    final protected function loadForeignKeys(Entity $entity): void
    {
        $entityClass = \get_class($entity);
        $reflexion = new EntityReflexion($entityClass);
        $foreignKeys = $reflexion->getForeignKeys();

        /** @var Entity $class */
        foreach ($foreignKeys as $property => $class) {

            $foreignKeyReflexion = $reflexion->getProperty($property);

            if (!($foreignKeyReflexion instanceof ReflectionProperty)) {
                throw new EntityException('The `' . $entityClass . '::' . $property . '` property does not exist');
            }

            $foreignKeyReflexion->setAccessible(true);
            $foreignKeyValue = $foreignKeyReflexion->getValue($entity);

            $foreignKeyClassReflexion = new EntityReflexion($class);
            $foreignKeyClassPrimaryKey = $foreignKeyClassReflexion->getPrimaryKey();

            $query = $this->_query_builder->select()->where($foreignKeyClassPrimaryKey->getName() . ' = :id')->setTable($class::getTable());

            try {
                $statement = $this->_pdo->prepare($query->build());
                $statement->bindParam(':id', $foreignKeyValue, PDO::PARAM_INT);
                $statement->execute();
            } catch (\PDOException $e) {
                throw new EntityRepositoryException($e->getMessage());
            }

            $results = $this->_statement_formatter->format($statement)->fetchObject($class);
            if (\count($results) > 0) {
                $foreignKey = $results[0];
                $this->load($foreignKey);
                $foreignKeyReflexion->setValue($entity, $foreignKey);
            } else {
                throw new EntityRepositoryException('The `' . $class . '` entity with id=' . $foreignKeyValue . ' has not been found');
            }

            $foreignKeyReflexion->setAccessible(false);
        }
    }

    /**
     * @param Entity $entity
     * @throws EntityException
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     * @throws StatementException
     */
    final protected function loadCollections(Entity $entity): void
    {
        $entityClass = \get_class($entity);
        $reflexion = new EntityReflexion($entity);
        $entityPrimaryKey = $reflexion->getPrimaryKey();
        $collections = $reflexion->getCollections();

        $entityPrimaryKey->setAccessible(true);
        $entityPrimaryKeyValue = $entityPrimaryKey->getValue($entity);
        $entityPrimaryKey->setAccessible(false);

        /** @var Entity $class */
        foreach ($collections as $property => $class) {

            $query = $this->_query_builder->select()
                ->where($entity::getTable() . ' = :' . $entity::getTable())
                ->setTable($entity::getTable() . '_' . $property)
                ->build();

            try {
                $statement = $this->_pdo->prepare($query);
                $statement->bindParam(':' . $entity::getTable(), $entityPrimaryKeyValue, PDO::PARAM_INT);
                $statement->execute();
            } catch (\PDOException $e) {
                throw new EntityRepositoryException($e->getMessage());
            }

            $results = $this->_statement_formatter->format($statement)->fetchAssoc();

            $collectionIds = [];
            $collectionItemTable = $class::getTable();
            foreach ($results as $result) {
                if (!\in_array((int)$result[$collectionItemTable], $collectionIds, true)) {
                    $collectionIds[] = (int)$result[$collectionItemTable];
                }
            }

            if (count($collectionIds) > 0) {
                $itemCollectionPrimaryKey = (new EntityReflexion($class))->getPrimaryKey();
                $query = $this->_query_builder->select()
                    ->where('`' . $itemCollectionPrimaryKey->getName() . '` IN (' . implode(', ', $collectionIds) . ')')
                    ->setTable($class::getTable())
                    ->build();

                try {
                    $statement = $this->_pdo->query($query);
                } catch (\PDOException $e) {
                    throw new EntityRepositoryException($e->getMessage());
                }

                $results = $this->_statement_formatter->format($statement)->fetchObject($class);

                $propertyReflexion = $reflexion->getProperty($property);

                if ($propertyReflexion instanceof ReflectionProperty) {
                    $propertyReflexion->setAccessible(true);
                    $propertyReflexion->setValue($entity, $results);
                    $propertyReflexion->setAccessible(false);
                } else {
                    throw new EntityException('The `' . $entityClass . '::' . $property . '` property does not exist');
                }
            }
        }
    }
}