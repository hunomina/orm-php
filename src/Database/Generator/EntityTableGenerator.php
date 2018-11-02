<?php

namespace hunomina\Orm\Database\Generator;

use hunomina\Orm\Database\Ddl\DdlException;
use hunomina\Orm\Database\Ddl\EntityDdlFactory;
use hunomina\Orm\Entity\Entity;
use PDO;

class EntityTableGenerator
{
    /** @var string $_entity */
    protected $_entity;

    /** @var PDO $_pdo */
    protected $_pdo;

    /** @var string $_type */
    protected $_type;

    /**
     * EntityTableGenerator constructor.
     * @param PDO $pdo
     * @param string $type
     */
    public function __construct(PDO $pdo, string $type = 'mysql')
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_pdo = $pdo;
        $this->_type = $type;
    }

    /**
     * @param string $entity
     * @return bool
     * @throws DdlException
     * @throws GeneratorException
     */
    public function generateEntityTable(string $entity): bool
    {
        if (class_exists($entity)) {
            if (is_subclass_of($entity, Entity::class)) {

                $entityDdl = EntityDdlFactory::get($entity, $this->_type);
                $doesTableExistStatement = $this->_pdo->query($entityDdl->doesTableExistDdl());

                if ($entityDdl->doesTableExistFromStatement($doesTableExistStatement)) {
                    try {
                        $entityDdl->setCurrentColumns($entityDdl->getColumnNamesFromStatement($this->_pdo->query($entityDdl->getColumnNamesDdl())));
                        $this->_pdo->exec($entityDdl->updateTableDdl());
                    } catch (\PDOException $e) {
                        throw new GeneratorException($e->getMessage());
                    }
                } else {
                    try {
                        $this->_pdo->exec($entityDdl->createTableDdl());
                    } catch (\PDOException $e) {
                        throw new GeneratorException($e->getMessage());
                    }
                }
            } else {
                throw new GeneratorException('The `' . $entity . '` class does not extend hunomina\Entity\Entity');
            }
        } else {
            throw new GeneratorException('The `' . $entity . '` class does not exist');
        }

        return true;
    }
}