<?php

class ModelHtml extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/html.png';
    }
    
    public function modelName($language=null)
    {
        return Yii::t('UnitHtml.main', 'HTML', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'widgets_html';
	}

	public function rules()
	{
		return $this->localizedRules(array(
            array('widget_id', 'required', 'on'=>'edit'),
			array('html', 'required'),
			array('widget_id', 'numerical', 'integerOnly'=>true),
		));
	}

    public function i18n()
    {
        return array('html');
    }


	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'widget_id' => 'Widget',
			'html' => Yii::t('UnitHtml.main', 'HTML'),
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
            'widget_id' => 'integer unsigned',
            'html' => 'text',
        );
    }   
}

