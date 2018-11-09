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
        $query = $this->_query_builder->select()->where('id = :id')->execute();
        $statement = $this->_pdo->prepare($query);
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();

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
        $query = $this->_query_builder->select()->execute();
        $statement = $this->_pdo->query($query);
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
        $reflexion = new EntityReflexion(\get_class($entity));
        $foreignKeys = $reflexion->getForeignKeys();

        /** @var Entity $class */
        foreach ($foreignKeys as $property => $class) {

            $query = $this->_query_builder->select()->where('id = :id')->setTable($class::getTable());
            $statement = $this->_pdo->prepare($query->execute());
            $statement->bindParam(':id', $entity->{$property}, PDO::PARAM_INT);
            $statement->execute();

            $results = $this->_statement_formatter->format($statement)->fetchObject($class);
            if (\count($results) > 0) {
                $foreignKey = $results[0];
                $this->load($foreignKey);
                $entity->{$property} = $foreignKey;
            } else {
                throw new EntityRepositoryException('The `' . $class . '` entity with id=' . $entity->{$property} . ' has not been found');
                // or null
            }
        }
    }

    /**
     * @param Entity $entity
     * @throws EntityException
     * @throws QueryBuilderException
     * @throws StatementException
     */
    final protected function loadCollections(Entity $entity): void
    {
        $reflexion = new EntityReflexion(\get_class($entity));
        $collections = $reflexion->getCollections();

        /** @var Entity $class */
        foreach ($collections as $property => $class) {

            $query = $this->_query_builder->select()
                ->where($entity::getTable() . ' = :' . $entity::getTable())
                ->setTable($entity::getTable() . '_' . $property)
                ->execute();

            $statement = $this->_pdo->prepare($query);
            $statement->bindParam(':' . $entity::getTable(), $entity->id, PDO::PARAM_INT);
            $statement->execute();

            $results = $this->_statement_formatter->format($statement)->fetchAssoc();

            $collectionIds = [];
            $collectionItemTable = $class::getTable();
            foreach ($results as $result) {
                if (!\in_array((int)$result[$collectionItemTable], $collectionIds, true)) {
                    $collectionIds[] = (int)$result[$collectionItemTable];
                }
            }

            $query = $this->_query_builder->select()
                ->where('id IN (' . implode(', ', $collectionIds) . ')')
                ->setTable($class::getTable())
                ->execute();

            $statement = $this->_pdo->query($query);
            $results = $this->_statement_formatter->format($statement)->fetchObject($class);
            $entity->{$property} = $results;
        }
    }
}