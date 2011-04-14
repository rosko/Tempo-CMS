<?php
class AuthAssignment extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->authManager->assignmentTable;
	}

    public function scheme()
    {
        return array(
            'itemname'=>'char(64)',
            'userid'=>'integer unsigned',
            'bizrule'=>'text',
            'data'=>'text',
        );
    }

    public function install()
    {
        $sql = 'alter table `' . self::tableName() . '` add primary key (`itemname`, `userid`)';
        Yii::app()->db->createCommand($sql)->execute();
        $sql = 'alter table `' . self::tableName() . '` add foreign key (`itemname`) references `'.AuthItem::tableName().'` (`name`) on delete cascade on update cascade';
        Yii::app()->db->createCommand($sql)->execute();
    }
}