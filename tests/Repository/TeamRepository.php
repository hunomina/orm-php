<?php

namespace hunomina\Orm\Tests\Repository;

use hunomina\Orm\EntityRepository\EntityRepository;
use hunomina\Orm\Tests\Entity\Team;

class TeamRepository extends EntityRepository
{
    protected function getEntityClass(): string
    {
        return Team::class;
    }
}