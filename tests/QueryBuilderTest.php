<?php

use hunomina\Orm\Database\QueryBuilder\MySql\MySqlQueryBuilder;
use hunomina\Orm\Database\QueryBuilder\QueryBuilderException;
use hunomina\Orm\Database\QueryBuilder\QueryBuilderFactory;

require_once __DIR__ . '/../vendor/autoload.php';

class QueryBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws QueryBuilderException
     */
    public function testSelect(): void
    {
        /** @var MySqlQueryBuilder $builder */
        $builder = QueryBuilderFactory::get('user', 'mysql');

        $selectQuery = $builder->select(['COUNT(*) as count'])
            ->addOrderBy('name DESC')
            ->addOrderBy('id DESC')
            ->addGroupBy('id')
            ->setLimit(10)
            ->setOffset(10)
            ->where('city = :city')
            ->where('country = :country')
            ->execute();

        $this->assertIsString($selectQuery);

        print "\nSelect Query : " . $selectQuery . "\n";
    }

    /**
     * @throws QueryBuilderException
     */
    public function testInsert(): void
    {
        /** @var MySqlQueryBuilder $builder */
        $builder = QueryBuilderFactory::get('user', 'mysql');

        $insertQuery = $builder->insert()
            ->setColumn('age', 10)
            ->setColumn('city', ':city')
            ->setColumn('country', 'France')
            ->setColumn('valid', true)
            ->setColumn('car', null)
            ->execute();

        $this->assertIsString($insertQuery);

        print "\nInsert Query : " . $insertQuery . "\n";
    }

    /**
     * @throws QueryBuilderException
     */
    public function testUpdate(): void
    {
        /** @var MySqlQueryBuilder $builder */
        $builder = QueryBuilderFactory::get('user', 'mysql');

        $updateQuery = $builder->update()
            ->addSet('name', ':name')
            ->addSet('pseudo', ':pseudo')
            ->addOrderBy('`name` DESC')
            ->setLimit(10)
            ->setOffset(2)
            ->where('`city` = :city')
            ->execute();

        $this->assertIsString($updateQuery);

        print "\nUpdate Query : " . $updateQuery . "\n";
    }

    /**
     * @throws QueryBuilderException
     */
    public function testDelete(): void
    {
        /** @var MySqlQueryBuilder $builder */
        $builder = QueryBuilderFactory::get('user', 'mysql');

        $deleteQuery = $builder->delete()
            ->addOrderBy('id DESC')
            ->addOrderBy('name DESC')
            ->setLimit(5)
            ->where('name LIKE :name')
            ->where('city = :city')
            ->execute();

        $this->assertIsString($deleteQuery);

        print "\nDelete Query : " . $deleteQuery . "\n";
    }
}