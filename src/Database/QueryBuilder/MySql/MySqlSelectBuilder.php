<?php

namespace hunomina\Orm\Database\QueryBuilder\MySql;

use hunomina\Orm\Database\QueryBuilder\BuilderInterface\SelectBuilder;

class MySqlSelectBuilder extends SelectBuilder
{
    public function execute(): string
    {
        $query = 'SELECT ';

        if (\count($this->_columns) > 0) {
            foreach ($this->_columns as $column) {
                $query .= $column . ', ';
            }
            $query = rtrim($query, ', ');
        } else {
            $query .= '*';
        }

        $query .= ' FROM `' . $this->_table . '`';

        if (\count($this->_where) > 0) {
            $where = ' WHERE ';
            foreach ($this->_where as $value) {
                $where .= $value . ' AND ';
            }
            $query .= rtrim($where, ' AND ');
        }

        if (\count($this->_group_by) > 0) {
            $groupBy = ' GROUP BY ';
            foreach ($this->_group_by as $group) {
                $groupBy .= $group . ', ';
            }
            $query .= rtrim($groupBy, ', ');
        }

        if (\count($this->_order_by) > 0) {
            $orderBy = ' ORDER BY ';
            foreach ($this->_order_by as $order) {
                $orderBy .= $order . ', ';
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