<?php

class UnitHeader extends Content
{
	const ICON = '/images/icons/fatcow/16x16/text_heading_1.png';
    const HIDDEN = false;

    public function unitName($language=null)
    {
        return Yii::t('UnitHeader.unit', 'Header', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_header';
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
            'header'=> Yii::t('UnitHeader.unit', 'Header type'),
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
                'header'=>array(
                    'type'=>'radiolist',
                    'items'=>array(
                        'h1' => Yii::t('UnitHeader.unit', 'First level'),
                        'h2' => Yii::t('UnitHeader.unit', 'Second level'),
                        'h3' => Yii::t('UnitHeader.unit', 'Third level'),
                    ),
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
            'header' => 'char(32)',
        );
    }

    public function prepare($params)
    {
        $params = parent::prepare($params);
        if ($params['unit']->title == '') {
            $params['unit']->title = '&nbsp;';
        }
        return $params;
    }
}