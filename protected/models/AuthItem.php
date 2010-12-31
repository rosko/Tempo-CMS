<?php

class AuthItem extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->authManager->itemTable;
	}

    public function scheme()
    {
        // primary key (name)
        return array(
            'name' => 'char(64)',
            'type' => 'integer',
            'description' => 'text',
            'bizrule' => 'text',
            'data' => 'text',
        );
    }

    public function install()
    {
        $sql = 'alter table `' . self::tableName() . '` add primary key (`name`)';
        Yii::app()->db->createCommand($sql)->execute();        
    }
    
}