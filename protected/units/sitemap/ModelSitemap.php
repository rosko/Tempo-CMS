<?php

class ModelSitemap extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/sitemap_color.png';
    }
    
    public function modelName($language=null)
    {
        return Yii::t('UnitSitemap.main', 'Sitemap', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'widgets_sitemap';
	}

	public function rules()
	{
		return array(
            array('widget_id', 'required', 'on'=>'edit'),
			array('widget_id, length, recursive, page, per_page', 'numerical', 'integerOnly'=>true),
            array('show_title', 'boolean'),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'widget_id' => 'Widget',
			'length' => Yii::t('UnitSitemap.main', 'Descriptions\' length'),
            'recursive' => Yii::t('UnitSitemap.main', 'Levels depth'),
            'page' => Yii::t('UnitSitemap.main', 'Parent page'),
            'show_title' => Yii::t('UnitSitemap.main', 'Show header'),
			'per_page' => Yii::t('UnitSitemap.main', 'Entries per page'),
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
                'show_title'=>array(
                    'type'=>'checkbox',
                ),
				'length'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 1,
						'max' => 2000,
						'step' => 50,
					)
				),
				'recursive'=>array(
					'type'=>'Slider',
                    'hint'=>Yii::t('UnitSitemap.main', 'If zero choosed, siblings pages will show'),
					'options'=>array(
						'min' => 0,
						'max' => 10,
					)
				),                
				'per_page'=>array(
					'type'=>'Slider',
                    'hint'=>Yii::t('UnitSitemap.main', 'If zero choosed, accordingly site\'s general settings'),
					'options'=>array(
						'min' => 0,
						'max' => 25,
					)
				),
				'page'=>array(
					'type'=>'PageSelect',
                    'excludeCurrent'=>false,
				),
			),
		);
	}

    public function scheme()
    {
        return array(
            'widget_id' => 'integer unsigned',
            'length' => 'integer unsigned',
            'recursive' => 'integer unsigned',
            'page' => 'integer unsigned',
            'show_title' => 'boolean',
            'per_page' => 'integer unsigned',
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
            '{$title}' => Yii::t('UnitSitemap.main', 'Header'),
            '{$items}' => Yii::t('UnitSitemap.main', 'Entries'),
            '{$count_items}' => Yii::t('UnitSitemap.main', 'Entries quantity'),
            '{$pager}' => Yii::t('UnitSitemap.main', 'Pager'),
        );
    }

}
