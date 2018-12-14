<?php

namespace hunomina\Orm\Database\QueryBuilder\MySql;

use hunomina\Orm\Database\QueryBuilder\BuilderInterface\DeleteBuilder;

class MySqlDeleteBuilder extends DeleteBuilder
{
    public function build(): string
    {
        $query = 'DELETE FROM `' . $this->_table . '`';

        if (\count($this->_where) > 0) {
            $where = ' WHERE ';
            foreach ($this->_where as $value) {
                $where .= $value . ' AND ';
            }
            $query .= rtrim($where, ' AND ');
        }

        if ($this->_limit !== null && $this->_limit >= 0) {
            if (\count($this->_order_by) > 0) {
                $orderBy = ' ORDER BY ';
                foreach ($this->_order_by as $order) {
                    $orderBy .= $order . ', ';
                }
                $query .= rtrim($orderBy, ', ');
            }
            $query .= ' LIMIT ' . $this->_limit;
        }

        // limit
        return $query . ';';
    }
}