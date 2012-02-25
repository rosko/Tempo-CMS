<?php

class ModelBreadcrumbs extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/hand_point.png';
    }
    
    public function modelName($language=null)
    {
        return Yii::t('UnitBreadcrumbs.main', 'Breadcrumbs', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_breadcrumbs';
	}

	public function rules()
	{
		return array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
			array('separator', 'length', 'max'=>16, 'encoding'=>'UTF-8'),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
            'separator'=> Yii::t('UnitBreadcrumbs.main', 'Separator'),
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
                'separator'=>array(
                    'type'=>'text'
                ),
                Yii::t('UnitBreadcrumbs.main', 'If empty, use \'<b>{separator}</b>\'', array('{separator}'=>WidgetBreadcrumbs::DEFAULT_SEPARATOR)),
			),
		);
	}

    public function scheme()
    {
        return array(
            'unit_id' => 'integer unsigned',
            'separator' => 'char(32)',
        );
    }

    public function  cacheDependencies() {
        $ids = str_replace('0,','',Yii::app()->page->model->path) . ',' . Yii::app()->page->model->id;
        return array(
            array(
                'class'=>'system.caching.dependencies.CDbCacheDependency',
                'sql'=>'SELECT MAX(`modify`) FROM `' . Page::tableName() . '` WHERE id in ('.$ids.')',
            ),
        );
    }

    public function templateVars()
    {
        return array(
            '{breadcrumbs separator=$separator homeLink=$homeLink  links=$links}' => Yii::t('UnitBreadcrumbs.main', 'Breadcrumbs'),
            '{$separator}' => Yii::t('UnitBreadcrumbs.main', 'Separator'),
            '{$homeLink}' => Yii::t('UnitBreadcrumbs.main', 'Caption or link for homepage'),
            '{$links}' => Yii::t('UnitBreadcrumbs.main', 'Links'),
        );
    }

}

