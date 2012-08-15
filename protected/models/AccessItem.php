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

}