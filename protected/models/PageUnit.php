<?php

class PageUnit extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'pages_units';
	}

	public function rules()
	{
		return array(
			array('page_id, unit_id, area, order', 'required'),
			array('page_id, unit_id, order', 'numerical', 'integerOnly'=>true),
			array('area', 'length', 'max'=>32),
		);
	}

	public function relations()
	{
		return array(
			'unit' => array(self::BELONGS_TO, 'Unit', 'unit_id')
		);
	}
	
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'page_id' => 'Page',
			'unit_id' => 'Unit',
			'area' => 'Area',
			'order' => 'Order',
		);
	}
	
	public function setBetween($ids)
	{
		
	}
	
	public function getBetween()
	{
		
	}
	
	public function getUnitIdById($id)
	{
		$sql = 'SELECT unit_id FROM `' . self::tableName() . '` WHERE id = :id';
		$command = Yii::app()->createCommand($sql);
		$command->bindValue(':id', intval($id), PDO::PARAM_INT);
		return $command->queryScalar();
	}
	
}