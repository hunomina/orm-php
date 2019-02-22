<?php

namespace hunomina\Orm\Tests\Entity;

use hunomina\Orm\Entity\Entity;

class Team extends Entity
{
    /**
     * @var int $id
     * @Id
     */
    private $id_team;

    /**
     * @var string $name
     * @DbType varchar(50)
     * @NotNull
     */
    private $name;

    /**
     * @var User[] $members
     * @Collection hunomina\Orm\Tests\Entity\User
     */
    private $members = [];

    public static function getTable(): string
    {
        return 'teams';
    }

    /**
     * @return int
     */
    public function getIdTeam(): int
    {
        return $this->id_team;
    }

    /**
     * @param int $id_team
     */
    public function setId(int $id_team): void
    {
        $this->id_team = $id_team;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return User[]
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    /**
     * @param User[] $members
     */
    public function setMembers(array $members): void
    {
        $this->members = $members;
    }
}