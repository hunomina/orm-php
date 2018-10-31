<?php

namespace hunomina\Orm\Database\Ddl\MySql;

use hunomina\Orm\Database\Ddl\PropertyDdl;
use hunomina\Orm\Entity\Entity;

class MySqlPropertyDdl extends PropertyDdl
{
    /**
     * @return string
     * Return database code to create the property column
     */
    public function createColumnDdl(): string
    {
        if ($this->isForeignKey()){
            $type = 'INT(11)';
        } else {
            $type = strtoupper($this->_type);
        }

        $ddl = '`' . $this->_name . '` ' . $type;

        if ($this->_not_null) {
            $ddl .= ' NOT NULL';
        } else {
            $ddl .= ' NULL';
        }

        if ($this->_default) {
            if (strtolower($this->_default) === 'null') {
                $ddl .= ' DEFAULT NULL';
            } else {
                $ddl .= " DEFAULT '" . $this->_default . "'";
            }
        }

        if ($this->_auto_increment) {
            $ddl .= ' AUTO_INCREMENT';
        }

        if ($this->_comments) {
            $ddl .= " COMMENT '" . $this->_comments . "'";
        }

        if ($this->_primary_key) { // primary
            $ddl .= ', PRIMARY KEY (`' . $this->_name . '`)';
        } elseif ($this->_foreign_key) { // or foreign key, not both
            /** @var Entity $foreignEntity */
            $foreignEntity = $this->_foreign_key;
            $ddl .= ', FOREIGN KEY (`' . $this->_name . '`) REFERENCES `' . $foreignEntity::getTable() . '`(`id`)';
        }

        return $ddl;
    }

    /**
     * @return string
     * Return database code to update the property column during 'ALTER TABLE'
     */
    public function alterTableUpdateColumnDdl(): string
    {
        if ($this->isForeignKey()){
            $type = 'INT(11)';
        } else {
            $type = strtoupper($this->_type);
        }

        $ddl = 'MODIFY COLUMN `' . $this->_name . '` ' . $type;

        if ($this->_not_null) {
            $ddl .= ' NOT NULL';
        } else {
            $ddl .= ' NULL';
        }

        if ($this->_default) {
            if (strtolower($this->_default) === 'null') {
                $ddl .= ' DEFAULT NULL';
            } else {
                $ddl .= " DEFAULT '" . $this->_default . "'";
            }
        }

        if ($this->_auto_increment) {
            $ddl .= ' AUTO_INCREMENT';
        }

        if ($this->_comments) {
            $ddl .= " COMMENT '" . $this->_comments . "'";
        }

        return $ddl;
    }

    /**
     * @return string
     * Return database code to create the property column during 'ALTER TABLE'
     */
    public function alterTableCreateColumnDdl(): string
    {
        if ($this->isForeignKey()){
            $type = 'INT(11)';
        } else {
            $type = strtoupper($this->_type);
        }

        $ddl = 'ADD COLUMN `' . $this->_name . '` ' . $type;

        if ($this->_not_null) {
            $ddl .= ' NOT NULL';
        } else {
            $ddl .= ' NULL';
        }

        if ($this->_default) {
            if (strtolower($this->_default) === 'null') {
                $ddl .= ' DEFAULT NULL';
            } else {
                $ddl .= " DEFAULT '" . $this->_default . "'";
            }
        }

        if ($this->_auto_increment) {
            $ddl .= ' AUTO_INCREMENT';
        }

        if ($this->_comments) {
            $ddl .= " COMMENT '" . $this->_comments . "'";
        }

        return $ddl;
    }
}