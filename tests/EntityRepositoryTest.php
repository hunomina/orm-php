<?php

use hunomina\Orm\Database\QueryBuilder\QueryBuilderException;
use hunomina\Orm\Database\Statement\StatementException;
use hunomina\Orm\Entity\EntityException;
use hunomina\Orm\EntityRepository\EntityRepositoryException;
use hunomina\Orm\Tests\Entity\Car;
use hunomina\Orm\Tests\Entity\Team;
use hunomina\Orm\Tests\Entity\User;
use hunomina\Orm\Tests\Repository\CarRepository;
use hunomina\Orm\Tests\Repository\TeamRepository;
use hunomina\Orm\Tests\Repository\UserRepository;

require_once __DIR__ . '/../vendor/autoload.php';

class EntityRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var PDO $_pdo */
    private $_pdo;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->_pdo = new PDO('mysql:host=localhost;dbname=travis', 'travis', '');
        $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_pdo->exec('SET NAMES utf8');
    }

    public function __destruct()
    {
        $this->_pdo = null;
    }

    /**
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     * @throws StatementException
     * @throws EntityException
     */
    public function testGetOneEntity(): void
    {
        $userRepo = new UserRepository($this->_pdo, 'mysql');
        $user = $userRepo->find(1);
        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     * @throws StatementException
     * @throws EntityException
     */
    public function testGetAllEntity(): void
    {
        $userRepo = new UserRepository($this->_pdo, 'mysql');
        $users = $userRepo->findAll();
        $this->assertContainsOnlyInstancesOf(User::class, $users);
    }

    /**
     * @throws EntityException
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     * @throws StatementException
     */
    public function testGetOneEntityWithForeignKeys(): void
    {
        $carRepo = new CarRepository($this->_pdo, 'mysql');
        /** @var Car $car */
        $car = $carRepo->find(1);
        $this->assertInstanceOf(Car::class, $car);
        $this->assertInstanceOf(User::class, $car->owner);
    }

    /**
     * @throws EntityException
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     * @throws StatementException
     */
    public function testGetAllEntityWithForeignKeys(): void
    {
        $carRepo = new CarRepository($this->_pdo, 'mysql');
        $cars = $carRepo->findAll();
        $this->assertContainsOnlyInstancesOf(Car::class, $cars);
        foreach ($cars as $car) {
            $this->assertInstanceOf(User::class, $car->owner);
        }
    }

    /**
     * @throws EntityException
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     * @throws StatementException
     */
    public function testGetOneEntityWithCollections(): void
    {
        $teamRepo = new TeamRepository($this->_pdo, 'mysql');
        /** @var Team $team */
        $team = $teamRepo->find(1);
        $this->assertInstanceOf(Team::class, $team);
        $this->assertContainsOnlyInstancesOf(User::class, $team->members);
    }

    /**
     * @throws EntityException
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     * @throws StatementException
     */
    public function testGetAllEntityWithCollections(): void
    {
        $teamRepo = new TeamRepository($this->_pdo, 'mysql');
        $teams = $teamRepo->findAll();
        $this->assertContainsOnlyInstancesOf(Team::class, $teams);
        /** @var Team $team */
        foreach ($teams as $team) {
            $this->assertContainsOnlyInstancesOf(User::class, $team->members);
        }
    }
}