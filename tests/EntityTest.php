<?php

use hunomina\Orm\Database\Ddl\DdlException;
use hunomina\Orm\Database\Ddl\MySql\MySqlEntityDdl;
use hunomina\Orm\Database\Generator\EntityTableGenerator;
use hunomina\Orm\Entity\EntityException;
use hunomina\Orm\Entity\EntityReflexion;
use hunomina\Orm\Entity\PropertyAnnotation;
use hunomina\Orm\EntityManager\EntityManager;
use hunomina\Orm\EntityManager\EntityManagerException;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Entities/User.php';
require_once __DIR__ . '/Entities/Car.php';
require_once __DIR__ . '/Entities/Team.php';

class EntityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws EntityException
     */
    public function testInstanciation(): void
    {
        $reflexion = new EntityReflexion(User::class);
        $properties = $reflexion->getProperties();

        $this->assertIsArray($properties);

        foreach ($properties as $property) {
            $annotation = new PropertyAnnotation($property);
            $this->assertIsString($annotation->getAnnotation('var'));
        }
    }

    /**
     * @throws EntityException
     * @throws DdlException
     */
    public function testCreateDdl(): void
    {
        $ddl = new MySqlEntityDdl(new EntityReflexion(User::class));
        $this->assertIsString($ddl->createTableDdl());

        $ddl = new MySqlEntityDdl(new EntityReflexion(Car::class));
        $this->assertIsString($ddl->createTableDdl());
        $this->assertIsString($ddl->updateTableDdl());

        $ddl = new MySqlEntityDdl(new EntityReflexion(Team::class));
        $this->assertIsString($ddl->createTableDdl());
        $this->assertIsString($ddl->updateTableDdl());
    }

    /**
     * @throws DdlException
     * @throws EntityException
     * @throws \hunomina\Orm\Database\Generator\GeneratorException
     */
    public function testGenerator(): void
    {
        $pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'root');
        $generator = new EntityTableGenerator($pdo, 'mysql');

        $this->assertTrue($generator->generateEntityTable(User::class));
        $this->assertTrue($generator->generateEntityTable(Car::class));
        $this->assertTrue($generator->generateEntityTable(Team::class));
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

        $pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'root');
        $em = new EntityManager($pdo);
        $em->persist($user1)->persist($user2)->persist($car)->persist($team)->flush();
    }
}