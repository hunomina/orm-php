<?php

namespace hunomina\Orm\Tests\Entity;

use hunomina\Orm\Entity\Entity;

class User extends Entity
{
    /**
     * @var int $id
     * @Id
     */
    public $id_user;

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

    /**
     * @return string
     */
    public static function getTable(): string
    {
        return 'users';
    }
}