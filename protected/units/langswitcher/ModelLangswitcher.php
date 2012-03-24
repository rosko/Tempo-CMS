<?php

class ModelLangswitcher extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/style_go.png';
    }
        
    public function modelName($language=null)
    {
        return Yii::t('UnitLangswitcher.main', 'Language switcher', array(), null, $language);
    }
    
    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'widgets_langswitcher';
	}

	public function rules()
	{
		return array(
            array('widget_id', 'required', 'on'=>'edit'),
			array('widget_id', 'numerical', 'integerOnly'=>true),
		);
	}

    public function scheme()
    {
        return array(
            'widget_id' => 'integer unsigned',
        );
    }

    public function  cacheDependencies() {
        return array(
            array(
                'class'=>'system.caching.dependencies.CDbCacheDependency',
                'sql'=>'SELECT MAX(`modify`) FROM `' . Page::tableName() . '` WHERE id = :id',
                'params'=>array(
                    'id'=>Yii::app()->page->model->id,
                ),
            ),
        );
    }

}

