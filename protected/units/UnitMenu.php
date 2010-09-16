<?php

class UnitMenu extends CActiveRecord
{
	const NAME = "Меню";
	const ICON = '/images/icons/iconic/green/read_more_16x16.png';
    const HIDDEN = true;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_menu';
	}

	public function rules()
	{
		return array(
			array('unit_id, items', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'unit_id' => 'Unit',
			'items' => 'Items',
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
/*				'items'=>array(
					'type'=>'textarea',
					'rows'=>7,
					'cols'=>40
				),*/
			),
		);
	}
}