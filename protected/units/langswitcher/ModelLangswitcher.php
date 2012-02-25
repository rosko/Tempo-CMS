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
		return Yii::app()->db->tablePrefix . 'units_langswitcher';
	}

	public function rules()
	{
		return array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
		);
	}

    public function scheme()
    {
        return array(
            'unit_id' => 'integer unsigned',
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

