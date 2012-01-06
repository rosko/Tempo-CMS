<?php

class UnitHeader extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/text_heading_1.png';
    }
    
    public function hidden()
    {
        return false;
    }
    
    public function unitName($language=null)
    {
        return Yii::t('UnitHeader.main', 'Header', array(), null, $language);
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
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
			array('header', 'length', 'max'=>20),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
            'header'=> Yii::t('UnitHeader.main', 'Header type'),
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
                'header'=>array(
                    'type'=>'ButtonSet',
                    'buttons'=>array(
                        'h1' => array(
                            'caption'=>'H1',
                            'title'=>Yii::t('UnitHeader.main', 'First level'),
                        ),
                        'h2' => array(
                            'caption'=>'H2',
                            'title'=>Yii::t('UnitHeader.main', 'Second level'),
                        ),
                        'h3' => array(
                            'caption'=>'H3',
                            'title'=>Yii::t('UnitHeader.main', 'Third level'),
                        ),
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

}

class UnitHeaderWidget extends ContentWidget
{
    public function init()
    {
        parent::init();
        if ($this->params['unit']->title == '') {
            $this->params['unit']->title = '&nbsp;';
        }
        
    }
}