<?php
class AuthItemChild extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->authManager->itemChildTable;
	}

    public function scheme()
    {
        return array(
            'parent'=>'char(64)',
            'child'=>'char(64)',
        );
    }

    public function install()
    {
        $sql = 'alter table `' . self::tableName() . '` add primary key (`parent`, `child`)';
        Yii::app()->db->createCommand($sql)->execute();
        $sql = 'alter table `' . self::tableName() . '` add foreign key (`parent`) references `'.AuthItem::tableName().'` (`name`) on delete cascade on update cascade';
        Yii::app()->db->createCommand($sql)->execute();
        $sql = 'alter table `' . self::tableName() . '` add foreign key (`child`) references `'.AuthItem::tableName().'` (`name`) on delete cascade on update cascade';
        Yii::app()->db->createCommand($sql)->execute();
    }

}