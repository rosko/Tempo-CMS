<?php

class ModelMenu extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/breeze.png';
    }
    
    public function modelName($language=null)
    {
        return Yii::t('UnitMenu.main', 'Menu', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_menu';
	}

	public function rules()
	{
		return array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id, recursive', 'numerical', 'integerOnly'=>true),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
//			'items' => 'Items',
            'recursive' => Yii::t('UnitMenu.main', 'Levels'),
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
				'recursive'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 1,
						'max' => 10,
					)
				),
                Yii::t('UnitMenu.main', 'If zero choosed, siblings pages will show'),
/*				'items'=>array(
					'type'=>'textarea',
					'rows'=>7,
					'cols'=>40
				),*/
			),
		);
	}

    public function scheme()
    {
        return array(
            'unit_id' => 'integer unsigned',
            'recursive' => 'integer unsigned',
            'items' => 'text',
        );
    }

    public function  cacheDependencies() {
        return array(
            array(
                'class'=>'system.caching.dependencies.CDbCacheDependency',
                'sql'=>'SELECT CONCAT(MAX(`modify`),MAX(`create`)) FROM `' . Page::tableName() . '`',
            ),
        );
    }

    public function templateVars()
    {
        return array(
            '{$tree}'=>Yii::t('UnitMenu.main', 'Menu items'),
        );
    }

}