<?php

class Unit extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units';
	}

	public function rules()
	{
		return array(
			array('type', 'required'),
			array('type', 'length', 'max'=>64),
			array('title', 'length', 'max'=>255),
		);
	}

	public function relations()
	{
		return array(
			'pages' => array(self::MANY_MANY, 'Page', 'pages_units(unit_id,page_id)')
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'type' => 'Тип',
			'title' => 'Название',
		);
	}

	public function getContent()
	{
		$tmp_class = 'Unit'.ucfirst(strtolower($this->type));
		return $tmp_class::model()->find('unit_id=:id', array(':id'=>$this->id));
	}

	public function getTypes()
	{
		$files = CFileHelper::findFiles(Yii::getPathOfAlias('application.units'),
			array('fileTypes'=>array('php'),
				  'level'=>0));
		$ret = array();
		foreach ($files as $f) {
			$p = pathinfo($f);
			$ret[] = $p['filename'];
		}
		return $ret;
	}
	
	public function beforeDelete()
	{
		return $this->content->delete();
	}

}