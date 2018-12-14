<?php

use hunomina\Orm\Database\Ddl\DdlException;
use hunomina\Orm\Database\QueryBuilder\QueryBuilderException;
use hunomina\Orm\Database\Statement\StatementException;
use hunomina\Orm\Entity\EntityException;
use hunomina\Orm\EntityManager\EntityManager;
use hunomina\Orm\EntityManager\EntityManagerException;
use hunomina\Orm\EntityRepository\EntityRepositoryException;
use hunomina\Orm\Tests\Entity\User;
use hunomina\Orm\Tests\Repository\UserRepository;

require_once __DIR__ . '/../vendor/autoload.php';

class EntityManagerTransactionTest extends PHPUnit\Framework\TestCase
{
    /** @var PDO $_pdo */
    private $_pdo;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->_pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'root');
    }

    public function __destruct()
    {
        $this->_pdo = null;
    }

    /**
     * @throws DdlException
     * @throws QueryBuilderException
     * @throws StatementException
     * @throws EntityManagerException
     * @throws EntityRepositoryException
     * @throws EntityException
     */
    public function testCommit(): void
    {
        $em = new EntityManager($this->_pdo, 'mysql');
        $user = new User();
        $user->name = 'commit';
        $user->email = 'commit@test.com';

        $em->beginTransaction()->persist($user)->flush();
        $em->commitTransaction();

        $userRepo = new UserRepository($this->_pdo, 'mysql');
        $found = $userRepo->findByEmail('commit@test.com');
        $this->assertInstanceOf(User::class, $found);

    }

    /**
     * @throws DdlException
     * @throws QueryBuilderException
     * @throws StatementException
     * @throws EntityManagerException
     * @throws EntityRepositoryException
     * @throws EntityException
     */
    public function testRollback(): void
    {
        $em = new EntityManager($this->_pdo, 'mysql');
        $user = new User();
        $user->name = 'rollback';
        $user->email = 'rollback@test.com';

        $em->beginTransaction()->persist($user)->flush();
        $em->rollbackTransaction();

        $userRepo = new UserRepository($this->_pdo, 'mysql');
        $found = $userRepo->findByEmail('rollback@test.com');
        $this->assertNotInstanceOf(User::class, $found);
    }
}