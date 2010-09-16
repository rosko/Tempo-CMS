<?php

class UnitBreadcrumbs extends CActiveRecord
{
	const NAME = "Путь к странице";
	const ICON = '/images/icons/iconic/green/read_more_16x16.png';
    const HIDDEN = true;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_breadcrumbs';
	}

	public function rules()
	{
		return array(
			array('unit_id', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
			array('separator', 'length', 'max'=>16),
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'unit_id' => 'Unit',
            'separator'=> 'Разделитель'
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
                'separator'=>array(
                    'type'=>'text'
                )
			),
		);
	}
}