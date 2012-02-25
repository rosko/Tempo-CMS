<?php

class ActiveRecord extends CActiveRecord 
{
    public function baseScheme()
    {
        return array(
            'id' => 'pk',
            'create' => 'timestamp not null default current_timestamp',
            'modify' => 'timestamp',            
        );
    }

    public function beforeSave()
    {
        if ($this->isNewRecord) 
        {
            if ($this->hasAttribute('create')) {
                $this->create = new CDbExpression('NOW()');
            }
        } else {
            if ($this->hasAttribute('modify')) {
                $this->modify = new CDbExpression('NOW()');
            }
        }
        return parent::beforeSave();
    }
    
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