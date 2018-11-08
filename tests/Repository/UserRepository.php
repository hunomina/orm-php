<?php

namespace hunomina\Orm\Tests\Repository;

use hunomina\Orm\EntityRepository\EntityRepository;
use hunomina\Orm\Tests\Entity\User;

class UserRepository extends EntityRepository
{
    protected function getEntityClass(): string
    {
        return User::class;
    }
}