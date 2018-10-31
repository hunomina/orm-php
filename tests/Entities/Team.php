<?php

use hunomina\Orm\Entity\Entity;

class Team extends Entity
{
    public static function getTable(): string
    {
        return 'team';
    }

    /**
     * @var string $name
     * @DbType varchar(50)
     * @NotNull
     */
    public $name;

    /**
     * @var User[] $members
     * @Collection User
     */
    public $members;
}