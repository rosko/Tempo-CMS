<?php

class ModelArea extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/html.png';
    }
    
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function modelName($language=null)
    {
        return Yii::t('UnitArea.main', 'Area of blocks', array(), null, $language);
    }
    
	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'widgets_area';
	}

	public function rules()
	{
		return array(
            array('widget_id', 'required', 'on'=>'edit'),
			array('widget_id, items', 'numerical', 'integerOnly'=>true),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'widget_id' => 'Widget',
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
            'widget_id' => 'integer unsigned',
            'items' => 'integer unsigned',
        );
    }
    
}

