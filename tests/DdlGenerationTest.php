<?php

use hunomina\Orm\Database\Ddl\DdlException;
use hunomina\Orm\Database\Ddl\EntityDdlFactory;
use hunomina\Orm\Entity\EntityException;
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
     * @throws DdlException
     * @throws EntityException
     */
    public function testCreateDdl(): void
    {
        $ddl = EntityDdlFactory::get(User::class, 'mysql');
        $this->assertIsString($ddl->createTableDdl());

        $ddl = EntityDdlFactory::get(Car::class, 'mysql');
        $this->assertIsString($ddl->createTableDdl());
        $this->assertIsString($ddl->updateTableDdl());

        $ddl = EntityDdlFactory::get(Team::class, 'mysql');
        $this->assertIsString($ddl->createTableDdl());
        $this->assertIsString($ddl->updateTableDdl());
    }
}