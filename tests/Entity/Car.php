<?php

namespace hunomina\Orm\Tests\Entity;

use hunomina\Orm\Entity\Entity;

class Car extends Entity
{
    /**
     * @var int $id
     * @Id
     * @Comments Primary key of the entity
     */
    public $id;

    /**
     * @var string $brand
     * @DbType varchar(30)
     * @Default null
     */
    public $brand;

    /**
     * @var string $model
     * @DbType varchar(50)
     * @Default ok
     * @NotNull
     */
    public $model;

    /**
     * @var User $owner
     * @DbType int(11)
     * @NotNull
     * @ForeignKey hunomina\Orm\Tests\Entity\User
     */
    public $owner;

    public static function getTable(): string
    {
        return 'car';
    }
}