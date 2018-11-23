<?php

namespace hunomina\Orm\Entity;

abstract class Entity
{
    /**
     * @return string
     */
    public static function getTable(): string
    {
        $explodedClass = explode('\\', __CLASS__);
        return array_pop($explodedClass);
    }
}