<?php

namespace hunomina\Orm\Database\QueryBuilder\BuilderInterface;

use hunomina\Orm\Database\QueryBuilder\QueryBuilderException;

abstract class InsertBuilder extends DdlBuilder
{
    /** @var string[] $_columns */
    protected $_columns = [];

    /**
     * @param string $column
     * @param $value
     * @return InsertBuilder
     * @throws QueryBuilderException
     */
    public function setColumn(string $column, $value): InsertBuilder
    {
        if (!is_scalar($value) && $value !== null) {
            throw new QueryBuilderException('The `' . $column . '` column can only be scalar');
        }
        $this->_columns[$column] = $value;
        return $this;
    }
}