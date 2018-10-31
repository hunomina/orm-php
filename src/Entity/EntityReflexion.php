<?php

namespace hunomina\Orm\Entity;

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
}