<?php

class ARRights extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->authManager->rightsTable;
	}

    public function scheme()
    {
        // primary key (name)
        return array(
            'itemname' => 'char(64)',
            'type' => 'integer',
            'weight' => 'integer',
        );
    }

    public function install()
    {
        $sql = 'alter table `' . self::tableName() . '` add primary key (`itemname`)';
        Yii::app()->db->createCommand($sql)->execute();
    }

}