<?php

namespace hunomina\Orm\Database\QueryBuilder\MySql;

use hunomina\Orm\Database\QueryBuilder\BuilderInterface\UpdateBuilder;
use hunomina\Orm\Database\QueryBuilder\QueryBuilderException;

class MySqlUpdateBuilder extends UpdateBuilder
{
    /**
     * @return string
     * @throws QueryBuilderException
     */
    public function execute(): string
    {
        $query = 'UPDATE `' . $this->_table . '`';

        if (\count($this->_sets) === 0) {
            throw new QueryBuilderException('The `sets` attribute can not be empty in `' . self::class . '`');
        }

        $query .= ' SET ';
        foreach ($this->_sets as $name => $value) {
            $query .= '`' . $name . '` = ' . $value . ', ';
        }
        $query = rtrim($query, ', ');

        if (\count($this->_where) > 0) {
            $where = ' WHERE ';
            foreach ($this->_where as $value) {
                $where .= $value . ' AND ';
            }
            $query .= rtrim($where, ' AND ');
        }

        if (\count($this->_order_by) > 0) {
            $orderBy = ' ORDER BY ';
            foreach ($this->_order_by as $value) {
                $orderBy .= $value . ', ';
            }
            $query .= rtrim($orderBy, ', ');
        }

        if ($this->_limit !== null && $this->_limit >= 0) {
            if ($this->_offset !== null && $this->_offset >= 0) {
                $query .= ' LIMIT ' . $this->_offset . ', ' . $this->_limit;
            } else {
                $query .= ' LIMIT 0, ' . $this->_limit;
            }
        }

        return $query . ';';
    }
}