<?php

namespace hunomina\Orm\Database\Ddl;

use hunomina\Orm\Entity\Entity;
use hunomina\Orm\Entity\PropertyAnnotation;

abstract class PropertyDdl
{
    private const TYPE_ANNOTATION_REGEXP = '/^@DbType ([a-zA-Z_()\d]+)$/';

    private const PRIMARY_KEY_ANNOTATION_REGEXP = '/^@PrimaryKey$/';

    private const FOREIGN_KEY_ANNOTATION_REGEXP = '/^@ForeignKey ([a-zA-Z\d\\_]+)$/';

    private const COLLECTION_ANNOTATION_REGEXP = '/^@Collection ([a-zA-Z\d\\_]+)$/';

    private const NOT_NULL_ANNOTATION_REGEXP = '/^@NotNull$/';

    private const AUTO_INCREMENT_ANNOTATION_REGEXP = '/^@AutoIncrement$/';

    private const DEFAULT_ANNOTATION_REGEXP = '/^@Default ([\S ]+)/';

    private const COMMENTS_ANNOTATION_REGEXP = '/^@Comments ([\S ]+)$/';

    private const IS_INT_REGEXP = '/^\d+$/';

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
        foreach ($annotations as $annotation) {
            if (strpos($annotation, '@Collection') === 0) {
                $collection = $annotation;
            }
            if (strpos($annotation, '@DbType') === 0) {
                $type = $annotation;
            }
            if (strpos($annotation, '@PrimaryKey') === 0) {
                $primaryKey = $annotation;
            }
            if (strpos($annotation, '@ForeignKey') === 0) {
                $foreignKey = $annotation;
            }
            if (strpos($annotation, '@NotNull') === 0) {
                $notNull = $annotation;
            }
            if (strpos($annotation, '@Default') === 0) {
                $default = $annotation;
            }
            if (strpos($annotation, '@Comments') === 0) {
                $comments = $annotation;
            }
            if (strpos($annotation, '@AutoIncrement') === 0) {
                $autoIncrement = $annotation;
            }
        }

        // checked first because needed later for @DbType annotation
        if (isset($collection)) {
            if (preg_match(self::COLLECTION_ANNOTATION_REGEXP, $collection, $match)) {
                $match = $match[1];
                if (class_exists($match)) {
                    if (is_subclass_of($match, Entity::class)) {
                        $this->_collection = $match;
                    } else {
                        throw new DdlException('The collection class for the `' . $this->_name . '` property does not extend hunomina\Entity\Entity');
                    }
                } else {
                    throw new DdlException('The collection class for the `' . $this->_name . '` property does not exist');
                }
            } else {
                throw new DdlException('The @Collection annotation is invalid for the `' . $this->_name . '` property');
            }
        }

        if (!$this->isCollection()) {
            if (isset($type)) {
                if (preg_match(self::TYPE_ANNOTATION_REGEXP, $type, $match)) {
                    $this->_type = $match[1];
                } else {
                    throw new DdlException('The @DbType annotation is invalid for the `' . $this->_name . '` property');
                }
            } else {
                throw new DdlException('The @DbType annotation has not been set for the `' . $this->_name . '` property');
            }
        }

        if (isset($primaryKey)) {
            if (preg_match(self::PRIMARY_KEY_ANNOTATION_REGEXP, $primaryKey)) {
                $this->_primary_key = true;
            } else {
                throw new DdlException('The @PrimaryKey annotation is invalid for the `' . $this->_name . '` property');
            }
        }

        if (isset($foreignKey)) {
            if (preg_match(self::FOREIGN_KEY_ANNOTATION_REGEXP, $foreignKey, $match)) {
                $match = $match[1];
                if (class_exists($match)) {
                    if (is_subclass_of($match, Entity::class)) {
                        $this->_foreign_key = $match;
                    } else {
                        throw new DdlException('The foreign key class for the `' . $this->_name . '` property does not extend hunomina\Entity\Entity');
                    }
                } else {
                    throw new DdlException('The foreign key class for the `' . $this->_name . '` property does not exist');
                }
            } else {
                throw new DdlException('The @ForeignKey annotation is invalid for the `' . $this->_name . '` property');
            }
        }

        if (isset($notNull)) {
            if (preg_match(self::NOT_NULL_ANNOTATION_REGEXP, $notNull)) {
                $this->_not_null = true;
            } else {
                throw new DdlException('The @NotNull annotation is invalid for the `' . $this->_name . '` property');
            }
        }

        if (isset($default)) {
            if (preg_match(self::DEFAULT_ANNOTATION_REGEXP, $default, $match)) {
                $match = $match[1];
                if (preg_match(self::IS_INT_REGEXP, $match)) {
                    $match = (int)$match;
                }
                $this->_default = $match;
            } else {
                throw new DdlException('The @Default annotation is invalid for the `' . $this->_name . '` property');
            }
        }

        if (isset($comments)) {
            if (preg_match(self::COMMENTS_ANNOTATION_REGEXP, $comments, $match)) {
                $this->_comments = $match[1];
            } else {
                throw new DdlException('The @Comments annotation is invalid for the `' . $this->_name . '` property');
            }
        }

        if (isset($autoIncrement)) {
            if (preg_match(self::AUTO_INCREMENT_ANNOTATION_REGEXP, $autoIncrement)) {
                $this->_auto_increment = true;
            } else {
                throw new DdlException('The @AutoIncrement annotation is invalid for the `' . $this->_name . '` property');
            }
        }
    }

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
    public function getForeignKeyClass(): string
    {
        return $this->_foreign_key;
    }

    /**
     * @return string
     */
    public function getCollectionClass(): string
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