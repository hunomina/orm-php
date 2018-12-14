<?php

namespace hunomina\Orm\EntityManager;

use hunomina\Orm\Database\Ddl\DdlException;
use hunomina\Orm\Database\Ddl\EntityDdlFactory;
use hunomina\Orm\Database\Ddl\PropertyDdl;
use hunomina\Orm\Entity\Entity;
use hunomina\Orm\Entity\EntityException;
use hunomina\Orm\Entity\EntityReflexion;
use PDO;
use PDOException;
use PDOStatement;
use SplObjectStorage;

class EntityManager
{
    /** @var \PDO $_pdo */
    private $_pdo;

    /** @var string $_type */
    private $_type;

    /**
     * @var SplObjectStorage $_add
     * List of entities to be create or update
     */
    private $_add;

    /**
     * @var SplObjectStorage $_delete
     * List of entities to be deleted
     */
    protected $_delete;

    /**
     * @var bool $_transaction_mode
     * Transaction mode enabled ? Otherwise auto commit mode is enabled
     */
    protected $_transaction_mode = false;

    /**
     * EntityManager constructor.
     * @param \PDO $pdo
     * @param string $type
     */
    public function __construct(\PDO $pdo, string $type = 'mysql')
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_pdo = $pdo;
        $this->_type = $type;
        $this->_add = new SplObjectStorage();
        $this->_delete = new SplObjectStorage();
    }

    /**
     * @return EntityManager
     * Enable transaction mode for $this->_pdo
     */
    public function beginTransaction(): EntityManager
    {
        $this->_pdo->beginTransaction();
        $this->_transaction_mode = true;
        return $this;
    }

    /**
     * @return EntityManager
     * @throws EntityManagerException
     * Commit $this->_pdo
     */
    public function commitTransaction(): EntityManager
    {
        if (!$this->_transaction_mode){
            throw new EntityManagerException('You can not commit, transaction mode is disabled');
        }

        $this->_pdo->commit();
        $this->_transaction_mode = false;
        return $this;
    }

    /**
     * @return EntityManager
     * @throws EntityManagerException
     * Rollback $this->_pdo
     */
    public function rollbackTransaction(): EntityManager
    {
        if (!$this->_transaction_mode){
            throw new EntityManagerException('You can not rollback, transaction mode is disabled');
        }

        $this->_pdo->rollBack();
        $this->_transaction_mode = false;
        return $this;
    }

    /**
     * @param Entity $entity
     * @return EntityManager
     */
    public function detach(Entity $entity): EntityManager
    {
        $this->_add->detach($entity);
        $this->_delete->detach($entity);
        return $this;
    }

    /**
     * Detach all attached entity to the EntityManager
     */
    public function reset(): void
    {
        $this->_add = new SplObjectStorage();
        $this->_delete = new SplObjectStorage();
    }

    /**
     * Add an entity to be deleted
     * @param Entity $entity
     * @return EntityManager
     * @throws EntityManagerException
     */
    public function delete(Entity $entity): EntityManager
    {
        if ($this->_add->contains($entity)) {
            throw new EntityManagerException('You can\'t delete an entity you want to add');
        }
        $this->_delete->attach($entity);
        return $this;
    }

    /**
     * @param Entity $entity
     * @return EntityManager
     * Add an entity to be created or updated
     * @throws EntityManagerException
     */
    public function persist(Entity $entity): EntityManager
    {
        if ($this->_delete->contains($entity)) {
            throw new EntityManagerException('You can\'t add an entity you want to delete');
        }
        $this->_add->attach($entity);
        return $this;
    }

    /**
     * @throws DdlException
     * @throws EntityException
     * @throws EntityManagerException
     * Update the database based on the registered entities
     */
    public function flush(): void
    {
        foreach ($this->_add as $entity) {
            if ($entity instanceof Entity) {
                $this->addEntity($entity);
            }
        }

        foreach ($this->_delete as $entity) {
            if ($entity instanceof Entity) {
                $entityDdl = EntityDdlFactory::get(\get_class($entity), $this->_type);
                $statement = $this->_pdo->prepare($entityDdl->deleteEntityDdl());
                $statement->bindParam(':id', $entity->id, PDO::PARAM_INT);
                try {
                    $statement->execute();
                } catch (PDOException $e) {
                    throw new EntityManagerException($e->getMessage());
                }
            }
        }

        // Detach entities after storage
        $this->reset();
    }

    /**
     * @param Entity $entity
     * @return bool
     * @throws DdlException
     * @throws EntityManagerException
     * @throws EntityException
     * Check if the entity already exist in the database
     */
    private function entityAlreadyExist(Entity $entity): bool
    {
        $entityClass = \get_class($entity);
        $entityPrimaryKey = (new EntityReflexion($entityClass))->getPrimaryKey();

        if ($entity->{$entityPrimaryKey->getName()} !== null) {
            $entityDdl = EntityDdlFactory::get($entityClass, $this->_type);

            $statement = $this->_pdo->prepare($entityDdl->doesEntityExistDdl());
            $statement->bindParam(':id', $entity->{$entityPrimaryKey->getName()}, \PDO::PARAM_INT);

            try {
                $statement->execute();
            } catch (PDOException $e) {
                throw new EntityManagerException($e->getMessage());
            }

            return $entityDdl->doesEntityExistFromStatement($statement);
        }

        return false;
    }

    /**
     * @param Entity $entity
     * @param array $collections
     * @throws DdlException
     * @throws EntityException
     * @throws EntityManagerException
     */
    private function saveCollections(Entity $entity, array $collections): void
    {
        $entityClass = \get_class($entity);
        $entityPrimaryKey = (new EntityReflexion($entityClass))->getPrimaryKey();
        $entityDdl = EntityDdlFactory::get($entityClass, $this->_type);

        foreach ($collections as $property) { // foreach collection property

            if ($property->isCollection()) {

                $this->emptyCollection($entity, $property);
                $items = $entity->{$property->getName()};
                $statement = null;

                foreach ($items as $item) { // foreach item of the collection
                    if ($item instanceof Entity) {

                        if (!($statement instanceof PDOStatement)) {
                            $statement = $this->_pdo->prepare($entityDdl->addCollectionItemDdl($entity, $property, $item)); // assuming that the type of $item does not change in this foreach
                        }

                        $this->addEntity($item); // need to store the child entity first
                        $itemPrimaryKey = (new EntityReflexion(\get_class($item)))->getPrimaryKey();

                        $statement->bindParam(':first_collection_item_id', $entity->{$entityPrimaryKey->getName()}, PDO::PARAM_INT);
                        $statement->bindParam(':second_collection_item_id', $item->{$itemPrimaryKey->getName()}, PDO::PARAM_INT);

                        try {
                            $statement->execute();
                        } catch (PDOException $e) {
                            throw new EntityManagerException($e->getMessage());
                        }
                    }
                }
            } else {
                throw new EntityManagerException('The `' . $property->getName() . '` of the `' . \get_class($entity) . '` is not a collection');
            }
        }
    }

    /**
     * @param Entity $entity
     * @param PropertyDdl $property
     * @throws DdlException
     * @throws EntityException
     * @throws EntityManagerException
     */
    private function emptyCollection(Entity $entity, PropertyDdl $property): void
    {
        $entityClass = \get_class($entity);
        $entityPrimaryKey = (new EntityReflexion($entityClass))->getPrimaryKey();
        $entityDdl = EntityDdlFactory::get($entityClass, $this->_type);

        if ($property->isCollection()) {
            $statement = $this->_pdo->prepare($entityDdl->emptyCollectionDdl($entity, $property));
            $statement->bindParam(':entity_id', $entity->{$entityPrimaryKey->getName()}, PDO::PARAM_INT);
            try {
                $statement->execute();
            } catch (PDOException $e) {
                throw new EntityManagerException($e->getMessage());
            }
        } else {
            throw new EntityManagerException('The `' . $property->getName() . '` of the `' . $entityClass . '` is not a collection');
        }
    }

    /**
     * @param Entity $entity
     * @return Entity
     * @throws DdlException
     * @throws EntityException
     * @throws EntityManagerException
     */
    private function addEntity(Entity $entity): Entity
    {
        $entityClass = \get_class($entity);
        $entityPrimaryKey = (new EntityReflexion($entityClass))->getPrimaryKey();
        $entityDdl = EntityDdlFactory::get($entityClass, $this->_type);

        $create = false;
        if ($this->entityAlreadyExist($entity)) {
            $ddl = $entityDdl->updateEntityDdl();
        } else {
            $ddl = $entityDdl->insertEntityDdl();
            $create = true;
        }

        $p = $entityDdl->getPropertiesDdl();
        $properties = [];
        $collectionProperties = [];

        foreach ($p as $property) {

            $name = $property->getName();
            if ($property->isForeignKey()) {
                $foreignKeyEntity = $property->getValue($entity);
                $foreignKeyPrimaryKey = (new EntityReflexion(\get_class($foreignKeyEntity)))->getPrimaryKey();
                $value = (int)$this->addEntity($foreignKeyEntity)->{$foreignKeyPrimaryKey->getName()};
            } elseif ($property->isCollection()) {
                $collectionProperties[] = $property;
                continue;
            } else {
                $value = $property->getValue($entity);
            }
            $properties[$name] = $value;
        }

        $statement = $this->_pdo->prepare($ddl);
        foreach ($properties as $name => &$value) {
            $name = ':' . $name;
            $statement->bindParam($name, $value);
        }
        unset($value);

        try {
            $statement->execute();
        } catch (PDOException $e) {
            throw new EntityManagerException($e->getMessage());
        }

        if ($create) {
            $entity->{$entityPrimaryKey->getName()} = (int)$this->_pdo->lastInsertId();
        }

        // collection need to be store after or the mother entity for foreign key to work
        $this->saveCollections($entity, $collectionProperties);

        return $entity;
    }
}