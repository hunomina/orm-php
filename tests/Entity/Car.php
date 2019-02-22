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
    private $id;

    /**
     * @var string $brand
     * @DbType varchar(30)
     * @Default null
     */
    private $brand;

    /**
     * @var string $model
     * @DbType varchar(50)
     * @Default ok
     * @NotNull
     */
    private $model;

    /**
     * @var User $owner
     * @DbType int(11)
     * @NotNull
     * @ForeignKey hunomina\Orm\Tests\Entity\User
     */
    private $owner;

    public static function getTable(): string
    {
        return 'cars';
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     */
    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @param string $model
     */
    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    /**
     * @return User
     */
    public function getOwner(): User
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }
}