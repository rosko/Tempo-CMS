<?php

class UnitHtml extends Content
{
	const NAME = "HTML-код";
	const ICON = '/images/icons/iconic/green/read_more_16x16.png';
    const HIDDEN = false;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_html';
	}

	public function rules()
	{
		return array(
			array('unit_id, html', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'unit_id' => 'Unit',
			'html' => 'HTML-код',
		);
	}

	public static function form()
	{
		return array(
            'title' => false,
			'elements'=>array(
				'html'=>array(
					'type'=>'textarea',
					'rows'=>15,
					'cols'=>80
				),
			),
		);
	}
}