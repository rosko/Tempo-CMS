<?php

class ModelHeader extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/text_heading_1.png';
    }
    
    public function modelName($language=null)
    {
        return Yii::t('UnitHeader.main', 'Header', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'widgets_header';
	}

	public function rules()
	{
		return array(
            array('widget_id', 'required', 'on'=>'edit'),
			array('widget_id', 'numerical', 'integerOnly'=>true),
			array('header', 'length', 'max'=>20),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'widget_id' => 'Widget',
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
            'widget_id' => 'integer unsigned',
            'header' => 'char(32)',
        );
    }

}
