<?php

class UnitHtml extends Content
{
	const ICON = '/images/icons/fatcow/16x16/html.png';
    const HIDDEN = false;

    public function unitName($language=null)
    {
        return Yii::t('UnitHtml.unit', 'HTML', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_html';
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
//			'id' => 'ID',
//			'unit_id' => 'Unit',
			'html' => Yii::t('UnitHtml.unit', 'HTML'),
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

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
            'create' => 'datetime',
            'modify' => 'datetime',
            'html' => 'text',
        );
    }
}