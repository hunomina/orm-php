<?php

use hunomina\Orm\Database\Ddl\MySql\MySqlEntityDdl;
use hunomina\Orm\Entity\EntityReflexion;
use hunomina\Orm\Tests\Entity\Car;
use hunomina\Orm\Tests\Entity\Team;
use hunomina\Orm\Tests\Entity\User;

require_once __DIR__ . '/../vendor/autoload.php';

class DdlGenerationTest extends \PHPUnit\Framework\TestCase
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
     * @throws \hunomina\Orm\Database\Ddl\DdlException
     * @throws \hunomina\Orm\Entity\EntityException
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
}