<?php

use hunomina\Orm\Entity\Entity;

class Car extends Entity
{
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
     * @ForeignKey User
     */
    public $owner;

    public static function getTable(): string
    {
        return 'car';
    }
}