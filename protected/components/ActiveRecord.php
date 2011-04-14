<?php

class ActiveRecord extends CActiveRecord {

    public function getAll($condition = '', $params = array(), $columns = '*')
    {
        $criteria=$this->getCommandBuilder()->createCriteria($condition,$params);
        $criteria->select = $columns;
        $this->beforeFind($criteria);
		$this->applyScopes($criteria);
        return $this->getCommandBuilder()->createFindCommand($this->getTableSchema(), $criteria)->queryAll();
    }

    public function getSql($columns='*')
    {
        $criteria=$this->getCommandBuilder()->createCriteria();
        $criteria->select = $columns;
        $this->beforeFind($criteria);
		$this->applyScopes($criteria);
        return $this->getCommandBuilder()->createFindCommand($this->getTableSchema(), $criteria)->getText();
    }

    public function getColumn($condition = '', $params = array(), $columns = '*')
    {
        $criteria=$this->getCommandBuilder()->createCriteria($condition,$params);
        $criteria->select = $columns;
        $this->beforeFind($criteria);
		$this->applyScopes($criteria);
        return $this->getCommandBuilder()->createFindCommand($this->getTableSchema(), $criteria)->queryColumn();
    }

}