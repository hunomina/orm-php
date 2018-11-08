<?php

namespace hunomina\Orm\Database\QueryBuilder\BuilderInterface;

abstract class DdlBuilder
{
    /** @var string $_table */
    protected $_table;

    /** @var string[] $_where */
    protected $_where = [];

    public function setTable(string $table): void
    {
        $this->_table = $table;
    }

    public function where(string $condition): DdlBuilder
    {
        if (!\in_array($condition, $this->_where, true)) {
            $this->_where[] = $condition;
        }
        return $this;
    }

    abstract public function execute(): string;
}