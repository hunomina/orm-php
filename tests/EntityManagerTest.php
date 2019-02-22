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
        $this->_pdo = new PDO('mysql:host=localhost;dbname=travis', 'travis', '');
        $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_pdo->exec('SET NAMES utf8');
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
    public function testInsert(): void
    {
        $ddl = new MySqlEntityDdl(new EntityReflexion(User::class));
        $this->assertIsString($ddl->insertEntityDdl());
        $this->assertIsString($ddl->updateEntityDdl());

        $user1 = new User();
        $user1->name = 'me';
        $user1->email = 'me@localhost.here';

        $user2 = new User();
        $user2->name = 'you';
        $user2->email = 'you@localhost.here';

        $car = new Car();
        $car->setOwner($user1);
        $car->setModel('Punto');
        $car->setBrand('Fiat');

        $team1 = new Team();
        $team1->setName('local1');
        $team1->setMembers([$user1, $user2]);

        $team2 = new Team();
        $team2->setName('local2');
        $team2->setMembers([$user1, $user2]);

        $em = new EntityManager($this->_pdo);
        $em->persist($user1)->persist($user2)->persist($car)->persist($team1)->persist($team2)->flush();
    }

    /**
     * @throws DdlException
     * @throws EntityException
     * @throws EntityManagerException
     */
    public function testUpdate(): void
    {
        $ddl = new MySqlEntityDdl(new EntityReflexion(User::class));
        $this->assertIsString($ddl->insertEntityDdl());
        $this->assertIsString($ddl->updateEntityDdl());

        $user1 = new User();
        $user1->id_user = 1;
        $user1->name = 'me2'; // change name
        $user1->email = 'me@localhost.here';

        // try on same class a second time
        $user2 = new User();
        $user2->id_user = 2;
        $user2->name = 'you2'; // change name
        $user2->email = 'you@localhost.here';

        $car = new Car();
        $car->setId(1);
        $car->setOwner($user1);
        $car->setModel('Punto2'); // change name
        $car->setBrand('Fiat');

        $team = new Team();
        $team->setId(1);
        $team->setName('local1renamed'); // change the name
        $team->setMembers([$user1]); // update collection

        $em = new EntityManager($this->_pdo);
        $em->persist($user1)->persist($user2)->persist($car)->persist($team)->flush();
    }

    /**
     * @throws DdlException
     * @throws EntityException
     * @throws EntityManagerException
     */
    public function testDelete(): void
    {
        $ddl = new MySqlEntityDdl(new EntityReflexion(User::class));
        $this->assertIsString($ddl->insertEntityDdl());
        $this->assertIsString($ddl->updateEntityDdl());

        $team = new Team();
        $team->setId(2);

        $em = new EntityManager($this->_pdo);
        $em->delete($team)->flush();
    }
}