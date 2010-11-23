<?php

class UnitHeader extends Content
{
	const NAME = "Заголовок";
	const ICON = '/images/icons/iconic/green/read_more_16x16.png';
    const HIDDEN = false;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_header';
	}

	public function rules()
	{
		return array(
			array('unit_id', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
			array('header', 'length', 'max'=>20),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
            'header'=> 'Тип заголовка',
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
                'header'=>array(
                    'type'=>'radiolist',
                    'items'=>array(
                        'h1' => 'Первого уровня',
                        'h2' => 'Второго уровня',
                        'h3' => 'Третьего уровня',
                    ),
                ),
			),
		);
	}

}