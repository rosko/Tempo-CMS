<?php

class UserRole extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return Yii::app()->db->tablePrefix . 'users_roles';
    }

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'user_id' => 'integer unsigned',
            'role_id' => 'integer unsigned',
        );
    }

    public function install()
    {
        $sql = 'alter table `' . self::tableName() . '` add unique (`user_id`, `role_id`)';
        Yii::app()->db->createCommand($sql)->execute();
    }

}