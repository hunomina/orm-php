<?php

namespace hunomina\Orm\Database\QueryBuilder\BuilderInterface;

abstract class DeleteBuilder extends DdlBuilder
{
    /** @var int $_limit */
    protected $_limit;

    /** @var string[] $_order_by */
    protected $_order_by = [];

    /**
     * @param int $limit
     * @return DeleteBuilder
     */
    public function setLimit(int $limit): DeleteBuilder
    {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * @param string $orderBy
     * @return DeleteBuilder
     */
    public function addOrderBy(string $orderBy): DeleteBuilder
    {
        if (!\in_array($orderBy, $this->_order_by, true)) {
            $this->_order_by[] = $orderBy;
        }
        return $this;
    }
}