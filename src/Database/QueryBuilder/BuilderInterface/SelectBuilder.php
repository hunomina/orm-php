<?php

namespace hunomina\Orm\Database\QueryBuilder\BuilderInterface;

use hunomina\Orm\Database\QueryBuilder\QueryBuilderException;

abstract class SelectBuilder extends DdlBuilder
{
    /** @var string[] $_order_by */
    protected $_order_by = [];

    /** @var string[] $_group_by */
    protected $_group_by = [];

    /** @var string[] $_columns */
    protected $_columns = [];

    /** @var int $_limit */
    protected $_limit;

    /** @var int $_offset */
    protected $_offset;

    /**
     * @param string $orderBy
     * @return SelectBuilder
     */
    public function addOrderBy(string $orderBy): SelectBuilder
    {
        if (!\in_array($orderBy, $this->_order_by, true)) {
            $this->_order_by[] = $orderBy;
        }
        return $this;
    }

    /**
     * @param string $groupBy
     * @return SelectBuilder
     */
    public function addGroupBy(string $groupBy): SelectBuilder
    {
        if (!\in_array($groupBy, $this->_group_by, true)) {
            $this->_group_by[] = $groupBy;
        }
        return $this;
    }

    /**
     * @param array $columns
     * @return SelectBuilder
     * @throws QueryBuilderException
     */
    public function setColumns(array $columns): SelectBuilder
    {
        foreach ($columns as $column) {
            if (\is_string($column)) {
                $this->addColumn($column);
            } else {
                throw new QueryBuilderException('A column can only be a string');
            }
        }
        return $this;
    }

    /**
     * @param string $column
     * @return SelectBuilder
     */
    public function addColumn(string $column): SelectBuilder
    {
        if (!\in_array($column, $this->_columns, true)) {
            $this->_columns[] = $column;
        }
        return $this;
    }

    /**
     * @param int $limit
     * @return SelectBuilder
     */
    public function setLimit(int $limit): SelectBuilder
    {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return SelectBuilder
     */
    public function setOffset(int $offset): SelectBuilder
    {
        $this->_offset = $offset;
        return $this;
    }
}