<?php

namespace hunomina\Orm\Tests\Entity;

use hunomina\Orm\Entity\Entity;

class Team extends Entity
{
    /**
     * @var int $id
     * @Id
     */
    public $id_team;

    /**
     * @var string $name
     * @DbType varchar(50)
     * @NotNull
     */
    public $name;

    /**
     * @var User[] $members
     * @Collection hunomina\Orm\Tests\Entity\User
     */
    public $members;

    public static function getTable(): string
    {
        return 'team';
    }
}