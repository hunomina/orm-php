<?php

namespace hunomina\Orm\Database\Ddl;

use hunomina\Orm\Entity\Entity;
use hunomina\Orm\Entity\EntityReflexion;
use PDOStatement;

/**
 * Class EntityDdl
 * @package hunomina\Entity\Ddl
 */
abstract class EntityDdl
{
    /** @var EntityReflexion $_entity_reflexion */
    protected $_entity_reflexion;

    /** @var string $_table */
    protected $_table;

    /** @var PropertyDdl[] $_properties_ddl */
    protected $_properties_ddl;

    /** @var string[] $_current_columns */
    protected $_current_columns = [];

    /**
     * EntityDdl constructor.
     * @param EntityReflexion $entity
     */
    public function __construct(EntityReflexion $entity)
    {
        $this->_entity_reflexion = $entity;
        $this->_table = $entity->getTable();
    }

    /**
     * @return PropertyDdl[]
     */
    public function getPropertiesDdl(): array
    {
        return $this->_properties_ddl;
    }

    /**
     * @return string
     * Return database code to create the entity table
     */
    abstract public function createTableDdl(): string;

    /**
     * @return string
     * Return database code to update the entity table
     */
    abstract public function updateTableDdl(): string;

    /**
     * @return string
     * Return database code to get columns from a table
     */
    abstract public function getColumnNamesDdl(): string;

    /**
     * @param PDOStatement $statement
     * @return array
     * Get column names from statement
     */
    abstract public function getColumnNamesFromStatement(PDOStatement $statement): array;

    /**
     * @param array $columns
     * @throws DdlException
     */
    public function setCurrentColumns(array $columns): void
    {
        foreach ($columns as $column) {
            if (!\is_string($column)) {
                throw new DdlException('Columns can only be strings');
            }
        }

        $this->_current_columns = $columns;
    }

    /**
     * @return string
     * Return database code to know if a the entity table exist
     */
    abstract public function doesTableExistDdl(): string;

    /**
     * @param PDOStatement $statement
     * @return bool
     */
    abstract public function doesTableExistFromStatement(PDOStatement $statement): bool;

    /**
     * @return string
     * Return database code to delete a specific entity
     * Must contain a specific flag :id to use with \PDO
     */
    abstract public function deleteEntityDdl(): string;

    /**
     * @return string
     * Return database code to insert a specific entity
     * Must contain a flag for each entity property except $_table to use with \PDO
     */
    abstract public function insertEntityDdl(): string;

    /**
     * @return string
     * Return database code to update a specific entity base on an :id flag
     * Must contain a flag for each entity property except $_table to use with \PDO
     */
    abstract public function updateEntityDdl(): string;

    /**
     * @return string
     * Return database code to check if an entity exist (need an :id flag to use with \PDO)
     */
    abstract public function doesEntityExistDdl(): string;

    /**
     * @param PDOStatement $statement
     * @return string
     */
    abstract public function doesEntityExistFromStatement(PDOStatement $statement): string;

    /**
     * @param array $properties
     * @return string
     * Return database code to create collections tables
     */
    abstract protected function createCollectionTablesIfNotExistDdl(array $properties): string;

    /**
     * @param Entity $entity
     * @param PropertyDdl $property
     * @param Entity $collectionItem
     * @return string
     * Return database code to add an item to a collection
     * Need to pass those parameters because table names can not be dynamically passed to \PDO
     * Need a :first_collection_item_id && a :second_collection_item_id flag to use with \PDO
     */
    abstract public function addCollectionItemDdl(Entity $entity, PropertyDdl $property, Entity $collectionItem): string;

    /**
     * @param Entity $entity
     * @param PropertyDdl $property
     * @return string
     * Return database code to empty a given collection for a given entity
     * Need an :entity_id flag to use with \PDO
     */
    abstract public function emptyCollectionDdl(Entity $entity, PropertyDdl $property): string;
}