<?php

namespace hunomina\Orm\Database\Ddl;

use hunomina\Orm\Database\Ddl\MySql\MySqlEntityDdl;
use hunomina\Orm\Entity\EntityException;
use hunomina\Orm\Entity\EntityReflexion;

abstract class EntityDdlFactory
{
    /**
     * @param string $entityClass
     * @param string $type
     * @return EntityDdl
     * @throws DdlException
     * @throws EntityException
     */
    public static function get(string $entityClass, string $type): EntityDdl
    {
        $entityReflexion = new EntityReflexion($entityClass);
        switch ($type) {
            case 'mysql':
                return new MySqlEntityDdl($entityReflexion);
                break;
            default:
                throw new DdlException('The `' . $type . '` type is not handled by the ORM');
                break;
        }
    }
}