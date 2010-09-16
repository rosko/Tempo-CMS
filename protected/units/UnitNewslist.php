<?php

class UnitNewslist extends Content
{
	const NAME = "Список новостей";
	const ICON = '/images/icons/iconic/green/document_fill_16x16.png';
    const HIDDEN = false;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_newslist';
	}

	public function rules()
	{
		return array(
			array('unit_id, rule', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
			array('rule', 'length', 'max'=>255),
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'unit_id' => 'Unit',
			'rule' => 'Правило'
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
				'rule'=>array(
					'type'=>'text',
					'maxlength'=>255,
					'size'=>30
				),
			),
		);
	}

}