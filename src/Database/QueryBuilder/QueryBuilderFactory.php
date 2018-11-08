<?php

namespace hunomina\Orm\Database\QueryBuilder;

use hunomina\Orm\Database\QueryBuilder\MySql\MySqlQueryBuilder;

abstract class QueryBuilderFactory
{
    /**
     * @param string $table
     * @param string $type
     * @return QueryBuilder
     * @throws QueryBuilderException
     */
    public static function get(string $table, string $type): QueryBuilder
    {
        switch ($type) {
            case 'mysql':
                return new MySqlQueryBuilder($table);
                break;
            default:
                throw new QueryBuilderException('The `' . $type . '` type is not handled by the QueryBuilder');
                break;
        }
    }
}