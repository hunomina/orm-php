<?php

namespace hunomina\Orm\Database\QueryBuilder\MySql;

use hunomina\Orm\Database\QueryBuilder\BuilderInterface\InsertBuilder;
use hunomina\Orm\Database\QueryBuilder\QueryBuilderException;

class MySqlInsertBuilder extends InsertBuilder
{
    /**
     * @return string
     * @throws QueryBuilderException
     */
    public function build(): string
    {
        $query = 'INSERT INTO `' . $this->_table . '`';

        if (\count($this->_columns) === 0) {
            throw new QueryBuilderException('Columns need to be set for an insert query');
        }

        $columns = '';
        $values = '';
        foreach ($this->_columns as $name => $value) {
            $columns .= '`' . $name . '`, ';
            if (\is_bool($value)) { // bool
                $values = $value ? $values . 'TRUE, ' : $values . 'FALSE, ';
            } elseif ($value === null) { // null
                $values .= 'NULL, ';
            } elseif (\is_numeric($value)) { // int
                $values .= $value . ', ';
            } else { // string
                $value = trim($value);
                if (strpos($value, ':') === 0 && \count(explode(' ', $value)) === 1) { // is flag
                    $values .= $value . ', ';
                } else {
                    $values .= "'" . $value . "', ";
                }
            }
        }
        $query .= ' (' . rtrim($columns, ', ') . ') VALUES (' . rtrim($values, ', ') . ')';

        return $query . ';';
    }
}