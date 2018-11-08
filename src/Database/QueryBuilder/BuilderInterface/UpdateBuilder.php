<?php

namespace hunomina\Orm\Database\QueryBuilder\BuilderInterface;

abstract class UpdateBuilder extends DdlBuilder
{
    protected $_sets = [];

    /** @var int $_limit */
    protected $_limit;

    /** @var int $_offset */
    protected $_offset;

    /** @var string[] $_order_by */
    protected $_order_by = [];

    public function addSet(string $column, $value): UpdateBuilder
    {
        $this->_sets[$column] = $value;
        return $this;
    }

    /**
     * @param int $limit
     * @return UpdateBuilder
     */
    public function setLimit(int $limit): UpdateBuilder
    {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return UpdateBuilder
     */
    public function setOffset(int $offset): UpdateBuilder
    {
        $this->_offset = $offset;
        return $this;
    }

    /**
     * @param string $orderBy
     * @return UpdateBuilder
     */
    public function addOrderBy(string $orderBy): UpdateBuilder
    {
        if (!\in_array($orderBy, $this->_order_by, true)) {
            $this->_order_by[] = $orderBy;
        }
        return $this;
    }
}