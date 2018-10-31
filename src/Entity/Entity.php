<?php

namespace hunomina\Orm\Entity;

abstract class Entity
{
    /**
     * @var int $id
     * @PrimaryKey
     * @AutoIncrement
     * @DbType int(11)
     * @Comments Primary key of the entity
     */
    public $id;

    abstract public static function getTable(): string;
}