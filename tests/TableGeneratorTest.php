<?php

use hunomina\Orm\Database\Ddl\DdlException;
use hunomina\Orm\Database\Generator\EntityTableGenerator;
use hunomina\Orm\Database\Generator\GeneratorException;
use hunomina\Orm\Entity\EntityException;
use hunomina\Orm\Tests\Entity\Car;
use hunomina\Orm\Tests\Entity\Team;
use hunomina\Orm\Tests\Entity\User;

require_once __DIR__ . '/../vendor/autoload.php';

class TableGeneratorTest extends \PHPUnit\Framework\TestCase
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
     * @throws GeneratorException
     * @throws EntityException
     */
    public function testGenerator(): void
    {
        $generator = new EntityTableGenerator($this->_pdo, 'mysql');

        $this->assertTrue($generator->generateEntityTable(User::class));
        $this->assertTrue($generator->generateEntityTable(Car::class));
        $this->assertTrue($generator->generateEntityTable(Team::class));
    }
}