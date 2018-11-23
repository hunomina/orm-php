<?php

namespace hunomina\Orm\Database\Ddl;

use hunomina\Orm\Entity\Entity;
use hunomina\Orm\Entity\PropertyAnnotation;

abstract class PropertyDdl
{
    public const TYPE_ANNOTATION_REGEXP = '/^@DbType ([a-zA-Z_()\d]+)$/';

    public const PRIMARY_KEY_ANNOTATION_REGEXP = '/^@Id$/';

    public const FOREIGN_KEY_ANNOTATION_REGEXP = '/^@ForeignKey ([a-zA-Z\d\\\_]+)$/';

    public const COLLECTION_ANNOTATION_REGEXP = '/^@Collection ([a-zA-Z\d\\\_]+)$/';

    public const NOT_NULL_ANNOTATION_REGEXP = '/^@NotNull$/';

    public const AUTO_INCREMENT_ANNOTATION_REGEXP = '/^@AutoIncrement$/';

    public const DEFAULT_ANNOTATION_REGEXP = '/^@Default ([\S ]+)/';

    public const COMMENTS_ANNOTATION_REGEXP = '/^@Comments ([\S ]+)$/';

    public const IS_INT_REGEXP = '/^\d+$/';

    /** @var string $_name */
    protected $_name;

    /** @var string $_type */
    protected $_type;

    /** @var PropertyAnnotation $_annotations */
    protected $_annotations;

    /** @var bool $_primary_key */
    protected $_primary_key = false;

    /** @var bool $_not_null */
    protected $_not_null = false;

    /** @var bool $_auto_increment */
    protected $_auto_increment = false;

    /** @var string $_default */
    protected $_default;

    /** @var string $_foreign_key */
    protected $_foreign_key;

    /** @var string $_collection */
    protected $_collection;

    /** @var string $_comments */
    protected $_comments;

    /**
     * PropertyDdl constructor.
     * @param \ReflectionProperty $property
     * @throws DdlException
     */
    public function __construct(\ReflectionProperty $property)
    {
        $this->_name = $property->getName();
        $this->_annotations = new PropertyAnnotation($property);

        $this->fetchAnnotations();
    }

    /**
     * @throws DdlException
     * Extract annotation values
     */
    protected function fetchAnnotations(): void
    {
        $annotations = $this->_annotations->getAnnotations();

        // get annotations value
        [$collection, $type, $primaryKey, $foreignKey, $notNull, $default, $comments, $autoIncrement] = null;
        foreach ($annotations as $annotation) {
            if (preg_match(self::COLLECTION_ANNOTATION_REGEXP, $annotation, $match)) {
                $collection = $match[1];
            }
            if (preg_match(self::TYPE_ANNOTATION_REGEXP, $annotation, $match)) {
                $type = $match[1];
            }
            if (preg_match(self::PRIMARY_KEY_ANNOTATION_REGEXP, $annotation)) {
                $primaryKey = true;
            }
            if (preg_match(self::FOREIGN_KEY_ANNOTATION_REGEXP, $annotation, $match)) {
                $foreignKey = $match[1];
            }
            if (preg_match(self::NOT_NULL_ANNOTATION_REGEXP, $annotation)) {
                $notNull = true;
            }
            if (preg_match(self::DEFAULT_ANNOTATION_REGEXP, $annotation, $match)) {
                $default = $match[1];
            }
            if (preg_match(self::COMMENTS_ANNOTATION_REGEXP, $annotation, $match)) {
                $comments = $match[1];
            }
            if (preg_match(self::AUTO_INCREMENT_ANNOTATION_REGEXP, $annotation)) {
                $autoIncrement = true;
            }
        }

        if ($primaryKey === true) {
            $this->_primary_key = true;
        }

        if ($foreignKey !== null) {
            if (class_exists($foreignKey)) {
                if (is_subclass_of($foreignKey, Entity::class)) {
                    $this->_foreign_key = $foreignKey;
                } else {
                    throw new DdlException('The foreign key class for the `' . $this->_name . '` property does not extend hunomina\Entity\Entity');
                }
            } else {
                throw new DdlException('The foreign key class for the `' . $this->_name . '` property does not exist');
            }
        }

        // checked first because needed later for @DbType annotation
        if ($collection !== null) {
            if (class_exists($collection)) {
                if (is_subclass_of($collection, Entity::class)) {
                    $this->_collection = $collection;
                } else {
                    throw new DdlException('The collection class for the `' . $this->_name . '` property does not extend hunomina\Entity\Entity');
                }
            } else {
                throw new DdlException('The collection class for the `' . $this->_name . '` property does not exist');
            }
        }

        if (!$this->isPrimaryKey() && !$this->isForeignKey() && !$this->isCollection()) {
            if ($type !== null) {
                $this->_type = $type;
            } else {
                throw new DdlException('The @DbType annotation has not been set or is invalid for the `' . $this->_name . '` property');
            }
        }

        if ($notNull === true) {
            $this->_not_null = true;
        }

        if ($default !== null) {
            if (preg_match(self::IS_INT_REGEXP, $default)) {
                $default = (int)$default;
            }
            $this->_default = $default;
        }

        if ($comments !== null) {
            $this->_comments = $comments;
        }

        if ($autoIncrement === true) {
            $this->_auto_increment = true;
        }

        /**
         * If the property is a primary key, it must be :
         * int
         * not null
         * auto increment
         * no default value
         * not be a foreign key
         * not be a collection
         */
        if ($this->isPrimaryKey()) {
            $this->_type = 'INT(11)';
            $this->_default = null;
            $this->_not_null = true;
            $this->_auto_increment = true;
            $this->_foreign_key = null;
            $this->_collection = null;
        }

        /**
         * If the property is a foreign key, it must be :
         * int
         * not a collection
         */
        if ($this->isForeignKey()) {
            $this->_type = 'INT(11)';
            $this->_collection = null;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * @return string
     * Return database code to create the property column
     */
    abstract public function createColumnDdl(): string;

    /**
     * @return string
     * Return database code to update the property column during 'ALTER TABLE'
     */
    abstract public function alterTableUpdateColumnDdl(): string;

    /**
     * @return string
     * Return database code to create the property column during 'ALTER TABLE'
     */
    abstract public function alterTableCreateColumnDdl(): string;

    /**
     * @return string
     */
    public function getForeignKeyClass(): ?string
    {
        return $this->_foreign_key;
    }

    /**
     * @return string
     */
    public function getCollectionClass(): ?string
    {
        return $this->_collection;
    }

    /**
     * @param Entity $entity
     * @return mixed
     * Return the given property from an entity
     */
    public function getValue(Entity $entity)
    {
        return $entity->{$this->_name};
    }

    /**
     * @return bool
     */
    public function isPrimaryKey(): bool
    {
        return $this->_primary_key;
    }

    /**
     * @return bool
     */
    public function isForeignKey(): bool
    {
        return $this->_foreign_key !== null;
    }

    /**
     * @return bool
     */
    public function isCollection(): bool
    {
        return $this->_collection !== null;
    }
}