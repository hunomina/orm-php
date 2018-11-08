<?php

namespace hunomina\EntityRepository;

use hunomina\Orm\Database\QueryBuilder\QueryBuilderException;
use hunomina\Orm\Database\QueryBuilder\QueryBuilderFactory;
use hunomina\Orm\Database\Statement\StatementException;
use hunomina\Orm\Database\Statement\StatementFormatter;
use hunomina\Orm\Entity\Entity;
use PDO;

abstract class EntityRepository
{
    /** @var string $_type */
    protected $_type;
    /**
     * @var PDO
     */
    protected $_pdo;

    /**
     * @var StatementFormatter $_statement_formatter
     */
    protected $_statement_formatter;

    /**
     * EntityRepository constructor.
     * @param PDO $pdo
     * @param string $type
     */
    public function __construct(PDO $pdo, string $type = 'mysql')
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_pdo = $pdo;
        $this->_type = $type;
        $this->_statement_formatter = new StatementFormatter();
    }

    /**
     * @param int $id
     * @return Entity|null
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     * @throws StatementException
     */
    public function find(int $id): ?Entity
    {
        $queryBuilder = QueryBuilderFactory::get($this->getEntityTable(), $this->_type);
        $query = $queryBuilder->select()->where('id = :id')->execute();
        $statement = $this->_pdo->prepare($query);
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $results = $this->_statement_formatter->format($statement)->fetchObject($this->getEntityClass());
        return \count($results) > 0 ? $results[0] : null;
    }

    /**
     * @return array
     * @throws EntityRepositoryException
     * @throws QueryBuilderException
     * @throws StatementException
     */
    public function findAll(): array
    {
        $queryBuilder = QueryBuilderFactory::get($this->getEntityTable(), $this->_type);
        $query = $queryBuilder->select()->execute();
        $statement = $this->_pdo->query($query);
        return $this->_statement_formatter->format($statement)->fetchObject($this->getEntityClass());
    }

    abstract protected function getEntityClass(): string;

    /**
     * @return string
     * @throws EntityRepositoryException
     */
    protected function getEntityTable(): string
    {
        $entityClass = $this->getEntityClass();
        if (class_exists($entityClass)) {
            if (is_subclass_of($entityClass, Entity::class)) {
                /** @var Entity $entityClass */
                return $entityClass::getTable();
            }
            throw new EntityRepositoryException('The Entity Repository can only use Entity classes');
        }
        throw new EntityRepositoryException('The `' . $entityClass . '` class does not exist');
    }
}