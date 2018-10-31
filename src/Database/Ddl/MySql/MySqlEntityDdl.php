<?php

namespace hunomina\Orm\Database\Ddl\MySql;

use hunomina\Orm\Database\Ddl\DdlException;
use hunomina\Orm\Database\Ddl\EntityDdl;
use hunomina\Orm\Database\Ddl\PropertyDdl;
use hunomina\Orm\Entity\Entity;
use hunomina\Orm\Entity\EntityReflexion;
use PDO;
use PDOStatement;

/**
 * Class MySqlEntityDdl
 * @package hunomina\Entity\Ddl\MySql
 */
class MySqlEntityDdl extends EntityDdl
{
    /**
     * MySqlEntityDdl constructor.
     * @param EntityReflexion $entity
     * @throws DdlException
     */
    public function __construct(EntityReflexion $entity)
    {
        parent::__construct($entity);

        foreach ($entity->getProperties() as $property) {
            $this->_properties_ddl[] = new MySqlPropertyDdl($property);
        }
    }

    /**
     * @return string
     * @throws DdlException
     */
    public function createTableDdl(): string
    {
        $collections = [];
        $ddl = 'CREATE TABLE `' . $this->_table . '` (';
        foreach ($this->_properties_ddl as $property) {
            if (!$property->isCollection()) {
                $ddl .= $property->createColumnDdl() . ', ';
            } else { // store collections to handle later
                $collections[] = $property;
            }
        }

        $ddl = rtrim($ddl, ', ') . ');';
        $ddl .= $this->createCollectionTablesIfNotExistDdl($collections);

        return $ddl;
    }

    /**
     * @return string
     * @throws DdlException
     */
    public function updateTableDdl(): string
    {
        $ddl = '';
        $collections = [];
        foreach ($this->_properties_ddl as $property) {
            if (!$property->isCollection()) {
                if (\in_array($property->getName(), $this->_current_columns, true)) {
                    $ddl .= 'ALTER TABLE `' . $this->_table . '` ' . $property->alterTableUpdateColumnDdl() . ';';
                } else {
                    $ddl .= 'ALTER TABLE `' . $this->_table . '` ' . $property->alterTableCreateColumnDdl() . ';';
                }
            } else { // store collections to handle later
                $collections[] = $property;
            }
        }
        $ddl .= $this->createCollectionTablesIfNotExistDdl($collections);

        return $ddl;
    }

    /**
     * @return string
     * Return database code to get columns from a table
     */
    public function getColumnNamesDdl(): string
    {
        return 'SHOW COLUMNS FROM `' . $this->_table . '`;';
    }

    /**
     * @param PDOStatement $statement
     * @return array
     * Fetch column names from statement
     */
    public function getColumnNamesFromStatement(PDOStatement $statement): array
    {
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $columns = [];
        foreach ($results as $result) {
            if (isset($result['Field'])) {
                $columns[] = $result['Field'];
            }
        }

        return $columns;
    }

    public function doesTableExistDdl(): string
    {
        return "SHOW TABLES LIKE '" . $this->_table . "'";
    }

    public function doesTableExistFromStatement(PDOStatement $statement): bool
    {
        return \count($statement->fetchAll(PDO::FETCH_ASSOC)) > 0;
    }

    public function deleteEntityDdl(): string
    {
        return 'DELETE FROM `' . $this->_table . '` WHERE id = :id';
    }

    public function insertEntityDdl(): string
    {
        $properties = [];
        foreach ($this->_properties_ddl as $property) {
            if (!$property->isCollection()) {
                $properties[] = $property->getName();
            }
        }

        $ddl = 'INSERT INTO `' . $this->_table . '` ';

        $columns = '';
        $flags = '';

        foreach ($properties as $property) {
            $columns .= '`' . $property . '`, ';
            $flags .= ':' . $property . ', ';
        }

        $columns = rtrim($columns, ', ');
        $flags = rtrim($flags, ', ');

        $ddl .= '(' . $columns . ') VALUES (' . $flags . ');';

        return $ddl;
    }

    /**
     * @return string
     * Return database code to update a specific entity
     * Must contain a specific flag :id to use with \PDO
     */
    public function updateEntityDdl(): string
    {
        $properties = [];
        foreach ($this->_properties_ddl as $property) {
            if (!$property->isCollection()) {
                $properties[] = $property->getName();
            }
        }

        $ddl = 'UPDATE `' . $this->_table . '` SET ';

        foreach ($properties as $property) {
            if ($property !== 'id') {
                $ddl .= '`' . $property . '` = :' . $property . ', ';
            }
        }

        $ddl = rtrim($ddl, ', ') . ' WHERE id = :id;';
        return $ddl;
    }

    /**
     * @return string
     * Return database code to check if an entity exist (need an :id flag to use with \PDO)
     */
    public function doesEntityExistDdl(): string
    {
        return 'SELECT COUNT(*) as count FROM `' . $this->_table . '` WHERE id = :id';
    }

    /**
     * @param PDOStatement $statement
     * @return string
     */
    public function doesEntityExistFromStatement(PDOStatement $statement): string
    {
        return (int)$statement->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }


    /**
     * @param array $properties
     * @return string
     * @throws DdlException
     */
    protected function createCollectionTablesIfNotExistDdl(array $properties): string
    {
        $ddl = '';
        foreach ($properties as $property) {
            if ($property instanceof PropertyDdl && $property->isCollection()) {

                $ddl .= 'CREATE TABLE IF NOT EXISTS `' . $this->_table . '_' . $property->getName() . '` (';
                /** @var Entity $collectionClass */
                $collectionClass = $property->getCollectionClass();
                $collectionTable = $collectionClass::getTable();

                $ddl .= '`id` INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`), ';
                $ddl .= '`' . $this->_table . '` INT(11) NOT NULL, FOREIGN KEY (`' . $this->_table . '`) REFERENCES `' . $this->_table . '`(`id`), ';
                $ddl .= '`' . $collectionTable . '` INT(11) NOT NULL, FOREIGN KEY (`' . $collectionTable . '`) REFERENCES `' . $collectionTable . '`(`id`)';

                $ddl .= ');';
            } else {
                throw new DdlException('The `' . $property->getName() . '` property of the `' . $this->_table . '` entity is not a collection');
            }
        }
        return $ddl;
    }

    public function addCollectionItemDdl(Entity $entity, PropertyDdl $property, Entity $collectionItem): string
    {
        $table = $entity::getTable() . '_' . $property->getName();
        return 'INSERT INTO `' . $table . '` (`' . $entity::getTable() . '`, `' . $collectionItem::getTable() . '`) VALUES (:first_collection_item_id, :second_collection_item_id);';
    }

    /**
     * @param Entity $entity
     * @param PropertyDdl $property
     * @return string
     * Return database code to empty a given collection for a given entity
     * Need an :entity_id flag to use with \PDO
     */
    public function emptyCollectionDdl(Entity $entity, PropertyDdl $property): string
    {
        $table = $entity::getTable() . '_' . $property->getName();
        return 'DELETE FROM `' . $table . '` WHERE `' . $entity::getTable() . '` = :entity_id';
    }
}