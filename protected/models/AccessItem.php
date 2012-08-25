<?php

class AccessItem extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return Yii::app()->db->tablePrefix . 'access';
    }

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'aco_class' => 'char(32)',
            'aco_key' => 'char(32)',
            'aco_value' => 'char(32)',
            'aro_class' => 'char(32)',
            'aro_key' => 'char(32)',
            'aro_value' => 'char(32)',
            'action' => 'char(32)',
            'is_deny' => 'boolean',
        );
    }

    public function getAroId()
    {
        return $this->aro_class . ':' . $this->aro_key . ':' . $this->aro_value;
    }

    public function getAroText($attribute='')
    {
        if (class_exists($this->aro_class)) {

            if ($this->aro_key == 'id' &&
                $obj = call_user_func(array($this->aro_class, 'model'))->findByPk($this->aro_value)) {

                return $obj->getRecordTitle();

            } else {

                return call_user_func(array($this->aro_class, 'model'))->getFewRecordsTitle($this->aro_key, $this->aro_value);

            }

        }
    }

}