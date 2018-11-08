<?php

namespace hunomina\Orm\Entity;

use hunomina\Orm\Database\Ddl\PropertyDdl;
use ReflectionProperty;

class EntityReflexion
{
    /** @var string $_table */
    private $_table;

    /** @var ReflectionProperty[] $properties */
    private $_properties;

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
                    $reflexion = new \ReflectionClass($entity);
                } catch (\ReflectionException $e) {
                    throw new EntityException($e->getMessage());
                }

                /** @var Entity $entity */
                $this->_table = $entity::getTable();
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

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->_table;
    }

    public function getProperties(): array
    {
        return $this->_properties;
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
     * @return string[]
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
}