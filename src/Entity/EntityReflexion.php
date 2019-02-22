<?php

namespace hunomina\Orm\Entity;

use hunomina\Orm\Database\Ddl\PropertyDdl;
use ReflectionClass;
use ReflectionProperty;

class EntityReflexion
{
    /** @var string $_table */
    private $_table;

    /** @var string $_entity_class */
    private $_entity_class;

    /** @var ReflectionProperty[] $properties */
    private $_properties;

    /** @var ReflectionClass $_reflexion */
    private $_reflexion;

    /**
     * EntityReflexion constructor.
     * @param string $entity
     * @throws EntityException
     */
    public function __construct(string $entity)
    {
        if (class_exists($entity)) {
            if (is_subclass_of($entity, Entity::class)) {

                try {
                    $reflexion = new ReflectionClass($entity);
                } catch (\ReflectionException $e) {
                    throw new EntityException($e->getMessage());
                }

                /** @var Entity $entity */
                $this->_entity_class = $entity;
                $this->_table = $entity::getTable();
                $this->_reflexion = $reflexion;
                $this->_properties = $reflexion->getProperties();
            } else {
                throw new EntityException("The class '" . $entity . "' does not extend '" . Entity::class . "'");
            }
        } else {
            throw new EntityException("The class '" . $entity . "' does not exist");
        }
    }

    public function getPropertiesName(): array
    {
        $names = [];
        foreach ($this->_properties as $property) {
            $names[] = $property->getName();
        }
        return $names;
    }

    public function getPropertyAnnotation(string $name): ?PropertyAnnotation
    {
        foreach ($this->_properties as $property) {
            if ($property->getName() === $name) {
                return new PropertyAnnotation($property);
            }
        }
        return null;
    }

    public function getEntityClass(): string
    {
        return $this->_entity_class;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->_table;
    }

    /**
     * @return ReflectionProperty[]
     */
    public function getProperties(): array
    {
        return $this->_properties;
    }

    /**
     * @param string $name
     * @return ReflectionProperty
     */
    public function getProperty(string $name): ?ReflectionProperty
    {
        foreach ($this->_properties as $property){
            if ($property->getName() === $name){
                return $property;
            }
        }

        return null;
    }

    /**
     * @return ReflectionClass
     */
    public function getReflexion(): ReflectionClass
    {
        return $this->_reflexion;
    }

    /**
     * @return string[]
     * @throws EntityException
     */
    public function getCollections(): array
    {
        $collections = [];

        /** @var ReflectionProperty $property */
        foreach ($this->getProperties() as $property) {
            $propertyAnnotation = new PropertyAnnotation($property);
            if ($annotation = $propertyAnnotation->getAnnotation('Collection')) {
                if (preg_match(PropertyDdl::COLLECTION_ANNOTATION_REGEXP, $annotation, $match) && $entityClass = $match[1]) {
                    if (class_exists($entityClass)) {
                        if (is_subclass_of($entityClass, Entity::class)) {
                            $collections[$property->name] = $entityClass;
                        } else {
                            throw new EntityException("The class '" . $entityClass . "' does not extend '" . Entity::class . "' and thereby can not be used for collections");
                        }
                    } else {
                        throw new EntityException("The class '" . $entityClass . "' does not exist and thereby can not be used for collections");
                    }
                } else {
                    throw new EntityException('The @Collection annotation has not been defined');
                }
            }
        }
        return $collections;
    }

    /**
     * @return ReflectionProperty[]
     * @throws EntityException
     */
    public function getForeignKeys(): array
    {
        $foreignKeys = [];

        /** @var ReflectionProperty $property */
        foreach ($this->getProperties() as $property) {
            $propertyAnnotation = new PropertyAnnotation($property);
            if ($annotation = $propertyAnnotation->getAnnotation('ForeignKey')) {
                if (preg_match(PropertyDdl::FOREIGN_KEY_ANNOTATION_REGEXP, $annotation, $match) && $entityClass = $match[1]) {
                    if (class_exists($entityClass)) {
                        if (is_subclass_of($entityClass, Entity::class)) {
                            $foreignKeys[$property->name] = $entityClass;
                        } else {
                            throw new EntityException("The class '" . $entityClass . "' does not extend '" . Entity::class . "' and thereby can not be used for foreign keys");
                        }
                    } else {
                        throw new EntityException("The class '" . $entityClass . "' does not exist and thereby can not be used for foreign keys");
                    }
                } else {
                    throw new EntityException('The @ForeignKey annotation has not been defined');
                }
            }
        }
        return $foreignKeys;
    }

    /**
     * @return ReflectionProperty
     * @throws EntityException
     */
    public function getPrimaryKey(): ReflectionProperty
    {
        /** @var ReflectionProperty $property */
        foreach ($this->getProperties() as $property) {
            $annotations = new PropertyAnnotation($property);
            if ($annotations->getAnnotation('Id')) {
                return $property;
            }
        }
        throw new EntityException('An entity must have a primary key');
    }
}