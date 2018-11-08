<?php

namespace hunomina\Orm\Tests\Repository;

use hunomina\Orm\EntityRepository\EntityRepository;
use hunomina\Orm\Tests\Entity\Car;

class CarRepository extends EntityRepository
{
    protected function getEntityClass(): string
    {
        return Car::class;
    }
}