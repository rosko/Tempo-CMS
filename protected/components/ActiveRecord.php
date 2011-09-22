<?php

class ActiveRecord extends CActiveRecord {

    public function getAll($condition = '', $params = array(), $columns = '*')
    {
//        if (is_string($condition)) {
            $criteria=$this->getCommandBuilder()->createCriteria($condition,$params);
            $criteria->select = $columns;
//        } else
//            $criteria = $condition;
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

        return array(
            'sql' => $this->getCommandBuilder()->createFindCommand($this->getTableSchema(), $criteria)->getText(),
            'params' => $criteria->params,
        );
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