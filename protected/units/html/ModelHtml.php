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
		return Yii::app()->db->tablePrefix . 'units_html';
	}

	public function rules()
	{
		return $this->localizedRules(array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('html', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
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
//			'unit_id' => 'Unit',
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
            'unit_id' => 'integer unsigned',
            'html' => 'text',
        );
    }   
}

