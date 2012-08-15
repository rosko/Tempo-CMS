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

    /**
     * Возвращает максимальное допустимое количество экземпляров данной модели
     * -1 - ни одного нельзя
     * 0 - без ограничений
     * >0 - конкретное ограничение
     *
     * @return int
     */
    public function maxLimit()
    {
        return 0;
    }

    public function isMaxLimitReached()
    {
        if ($this->maxLimit() < 0) {
            return true;
        } elseif ($this->maxLimit() > 0) {
            $count = Yii::app()->db->createCommand('SELECT count(*) FROM `' . $this->tableName() . '`')->queryScalar();
            return $count >= $this->maxLimit();
        } else {
            return false;
        }
    }

    public function beforeSave()
    {
        if ($this->isNewRecord) {

            if ($this->isMaxLimitReached())
                return false;

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
        //if (is_string($condition)) {
            $criteria = $this
                ->getCommandBuilder()
                ->createCriteria($condition, $params);
            $criteria->select = $columns;
        //} else {
        //    $criteria = $condition;
        //}
        $this->beforeFind($criteria);
        $this->applyScopes($criteria);
        return $this
            ->getCommandBuilder()
            ->createFindCommand($this->getTableSchema(), $criteria)->queryAll();
    }

    public function getSql($columns='*')
    {
        $criteria=$this->getCommandBuilder()->createCriteria();
        $criteria->select = $columns;
        $this->beforeFind($criteria);
        $this->applyScopes($criteria);

        return array(
            'sql' => $this
                ->getCommandBuilder()
                ->createFindCommand($this->getTableSchema(), $criteria)->getText(),
            'params' => $criteria->params,
        );
    }

    public function getColumn($condition = '', $params = array(), $columns = '*')
    {
        $criteria = $this->getCommandBuilder()->createCriteria($condition, $params);
        $criteria->select = $columns;
        $this->beforeFind($criteria);
        $this->applyScopes($criteria);
        return $this
            ->getCommandBuilder()
            ->createFindCommand($this->getTableSchema(), $criteria)->queryColumn();
    }

    public function searchAttributes()
    {
        return array();
    }

    protected function query($criteria,$all=false)
    {
        $this->beforeFind();
        $this->applyScopes($criteria);
        try {
            $this->allowed();
        } catch(Exception $e) { }

        if(empty($criteria->with))
        {
            if(!$all)
                $criteria->limit=1;
            $command=$this->getCommandBuilder()->createFindCommand($this->getTableSchema(),$criteria);
            return $all ? $this->populateRecords($command->queryAll(), true, $criteria->index) : $this->populateRecord($command->queryRow());
        }
        else
        {
            $finder=new CActiveFinder($this,$criteria->with);
            return $finder->query($criteria,$all);
        }
    }



}