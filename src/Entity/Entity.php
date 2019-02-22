<?php

namespace hunomina\Orm\Entity;

abstract class Entity
{
    /**
     * @return string
     */
    abstract public static function getTable(): string;
}