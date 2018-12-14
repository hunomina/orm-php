<?php

namespace hunomina\Orm\Tests\Repository;

use hunomina\Orm\Database\QueryBuilder\QueryBuilderException;
use hunomina\Orm\Database\QueryBuilder\QueryBuilderFactory;
use hunomina\Orm\Database\Statement\StatementException;
use hunomina\Orm\Database\Statement\StatementFormatter;
use hunomina\Orm\EntityRepository\EntityRepository;
use hunomina\Orm\Tests\Entity\User;

class UserRepository extends EntityRepository
{
    /**
     * @param string $email
     * @return User|null
     * @throws QueryBuilderException
     * @throws StatementException
     */
    public function findByEmail(string $email): ?User
    {
        $qb = QueryBuilderFactory::get(User::getTable(), 'mysql');
        $query = $qb->select()->where('email = :email')->build();

        $query = $this->_pdo->prepare($query);
        $query->bindParam(':email', $email);
        $query->execute();

        $sf = new StatementFormatter();
        $entities = $sf->format($query)->fetchObject(User::class);

        return count($entities) > 0 ? $entities[0] : null;
    }

    protected function getEntityClass(): string
    {
        return User::class;
    }
}