<?php

use hunomina\Orm\Database\Ddl\DdlException;
use hunomina\Orm\Database\Ddl\MySql\MySqlEntityDdl;
use hunomina\Orm\Entity\EntityException;
use hunomina\Orm\Entity\EntityReflexion;
use hunomina\Orm\EntityManager\EntityManager;
use hunomina\Orm\EntityManager\EntityManagerException;
use hunomina\Orm\Tests\Entity\Car;
use hunomina\Orm\Tests\Entity\Team;
use hunomina\Orm\Tests\Entity\User;

require_once __DIR__ . '/../vendor/autoload.php';

class EntityManagerTest extends \PHPUnit\Framework\TestCase
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
     * @throws EntityException
     * @throws EntityManagerException
     */
    public function testInsertUpdateDelete(): void
    {
        $ddl = new MySqlEntityDdl(new EntityReflexion(User::class));
        $this->assertIsString($ddl->insertEntityDdl());
        $this->assertIsString($ddl->updateEntityDdl());

        $user1 = new User();
        $user1->id = 1;
        $user1->name = 'me';
        $user1->email = 'me@localhost.here';

        $user2 = new User();
        $user2->id = 2;
        $user2->name = 'you';
        $user2->email = 'you@localhost.here';

        $car = new Car();
        $car->id = 1;
        $car->owner = $user1;
        $car->model = 'Punto';
        $car->brand = 'Fiat';

        $team = new Team();
        $team->id = 1;
        $team->name = 'local';
        $team->members = [$user1, $user2];

        $em = new EntityManager($this->_pdo);
        $em->persist($user1)->persist($user2)->persist($car)->persist($team)->flush();
    }
}