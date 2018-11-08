<?php

namespace hunomina\Orm\Database\QueryBuilder\MySql;

use hunomina\Orm\Database\QueryBuilder\BuilderInterface\{DeleteBuilder, InsertBuilder, SelectBuilder, UpdateBuilder};
use hunomina\Orm\Database\QueryBuilder\QueryBuilder;

class MySqlQueryBuilder extends QueryBuilder
{
    /**
     * @return SelectBuilder
     */
    public function getSelectBuilder(): SelectBuilder
    {
        return new MySqlSelectBuilder();
    }

    /**
     * @return UpdateBuilder
     */
    public function getUpdateBuilder(): UpdateBuilder
    {
        return new MySqlUpdateBuilder();
    }

    /**
     * @return InsertBuilder
     */
    public function getInsertBuilder(): InsertBuilder
    {
        return new MySqlInsertBuilder();
    }

    /**
     * @return DeleteBuilder
     */
    public function getDeleteBuilder(): DeleteBuilder
    {
        return new MySqlDeleteBuilder();
    }
}