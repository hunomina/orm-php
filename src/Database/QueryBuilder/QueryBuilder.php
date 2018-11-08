<?php

namespace hunomina\Orm\Database\QueryBuilder;

use hunomina\Orm\Database\QueryBuilder\BuilderInterface\{DeleteBuilder, InsertBuilder, SelectBuilder, UpdateBuilder};

abstract class QueryBuilder
{
    /** @var string $_type */
    protected $_table;

    /**
     * QueryBuilder constructor.
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->_table = $table;
    }

    /**
     * @return SelectBuilder
     */
    abstract protected function getSelectBuilder(): SelectBuilder;

    /**
     * @return UpdateBuilder
     */
    abstract protected function getUpdateBuilder(): UpdateBuilder;

    /**
     * @return InsertBuilder
     */
    abstract protected function getInsertBuilder(): InsertBuilder;

    /**
     * @return DeleteBuilder
     */
    abstract protected function getDeleteBuilder(): DeleteBuilder;

    /**
     * @param array $columns
     * @return SelectBuilder
     * @throws QueryBuilderException
     */
    public function select(array $columns = []): SelectBuilder
    {
        $builder = $this->getSelectBuilder();
        $builder->setTable($this->_table);
        $builder->setColumns($columns);
        return $builder;
    }

    /**
     * @return UpdateBuilder
     */
    public function update(): UpdateBuilder
    {
        $builder = $this->getUpdateBuilder();
        $builder->setTable($this->_table);
        return $builder;
    }

    /**
     * @return InsertBuilder
     */
    public function insert(): InsertBuilder
    {
        $builder = $this->getInsertBuilder();
        $builder->setTable($this->_table);
        return $builder;
    }

    /**
     * @return DeleteBuilder
     */
    public function delete(): DeleteBuilder
    {
        $builder = $this->getDeleteBuilder();
        $builder->setTable($this->_table);
        return $builder;
    }
}