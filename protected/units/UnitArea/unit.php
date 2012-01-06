<?php

class UnitArea extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/html.png';
    }
    public function hidden()
    {
        return false;
    }
    
    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function unitName($language=null)
    {
        return Yii::t('UnitArea.main', 'Area of blocks', array(), null, $language);
    }
	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_area';
	}

	public function rules()
	{
		return array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id, items', 'numerical', 'integerOnly'=>true),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
			'items' => '',
		);
	}

	public static function form()
	{
		return array(
            'title' => false,
			'elements'=>array(
				'items'=>array(
					'type'=>'AreaEdit',
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
            'items' => 'integer unsigned',
        );
    }
    
}

class UnitAreaWidget extends ContentWidget 
{
    public function init()
    {
        parent::init();
        $this->params['areaId'] = 'unit'.$this->params['unit']->id.'UnitArea_items';
        $this->params['pageUnits'] = PageUnit::model()->findAll(array(
            'condition' => '`area` = :area',
            'params' => array(
                'area' => $this->params['areaId']
            ),
            'with' => array('unit'),
            'order' => '`order`'
        ));
    }
    
}