<?php

use hunomina\Orm\Entity\Entity;

class User extends Entity
{
    /**
     * @var string $name
     * @DbType varchar(50)
     */
    public $name;

    /**
     * @var string $email
     * @DbType varchar(100)
     * @NotNull
     */
    public $email;

    public static function getTable(): string
    {
        return 'user';
    }
}