<?php

namespace hunomina\Orm\Database\Statement;

use PDO;
use PDOStatement;

class StatementFormatter
{
    protected $_statement;

    /**
     * @param PDOStatement $statement
     * @return StatementFormatter
     */
    public function format(PDOStatement $statement): StatementFormatter
    {
        $this->_statement = $statement;
        return $this;
    }

    /**
     * @return array
     * @throws StatementException
     */
    public function fetchAssoc(): array
    {
        if ($this->_statement instanceof PDOStatement) {
            return $this->_statement->fetchAll(PDO::FETCH_ASSOC);
        }
        throw new StatementException('You didn\'t passed a valid statement to format');
    }

    /**
     * @return array
     * @throws StatementException
     */
    public function fetchBoth(): array
    {
        if ($this->_statement instanceof PDOStatement) {
            return $this->_statement->fetchAll(PDO::FETCH_BOTH);
        }
        throw new StatementException('You didn\'t passed a valid statement to format');
    }

    /**
     * @return array
     * @throws StatementException
     */
    public function fetchNamed(): array
    {
        if ($this->_statement instanceof PDOStatement) {
            return $this->_statement->fetchAll(PDO::FETCH_NAMED);
        }
        throw new StatementException('You didn\'t passed a valid statement to format');
    }

    /**
     * @return array
     * @throws StatementException
     */
    public function fetchNum(): array
    {
        if ($this->_statement instanceof PDOStatement) {
            return $this->_statement->fetchAll(PDO::FETCH_NUM);
        }
        throw new StatementException('You didn\'t passed a valid statement to format');
    }

    /**
     * @param string $className
     * @param array $param
     * @return mixed
     * @throws StatementException
     */
    public function createObjectFrom(string $className, array $param)
    {
        if ($this->_statement instanceof PDOStatement) {
            if (class_exists($className)) {
                return $this->_statement->fetchObject($className, $param);
            }
            throw new StatementException('This class can not be used for fetching');
        }
        throw new StatementException('You didn\'t passed a valid statement to format');
    }

    /**
     * @param string $className
     * @return array
     * @throws StatementException
     */
    public function fetchObject(string $className): array
    {
        if ($this->_statement instanceof PDOStatement) {
            if (class_exists($className)) {
                return $this->_statement->fetchAll(PDO::FETCH_CLASS, $className);
            }
            throw new StatementException('This class can not be used for fetching');
        }
        throw new StatementException('You didn\'t passed a valid statement to format');
    }

    /**
     * @return StatementFormatter
     */
    public function clear(): StatementFormatter
    {
        $this->_statement = null;
        return $this;
    }
}