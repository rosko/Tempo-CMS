<?php

class UnitSitemap extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/sitemap_color.png';
    }
    
    public function hidden()
    {
        return true;
    }

    public function unitName($language=null)
    {
        return Yii::t('UnitSitemap.main', 'Sitemap', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_sitemap';
	}

	public function rules()
	{
		return array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id, length, recursive, page, per_page', 'numerical', 'integerOnly'=>true),
            array('show_title', 'boolean'),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
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
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
            'create' => 'datetime',
            'modify' => 'datetime',
            'length' => 'integer unsigned',
            'recursive' => 'integer unsigned',
            'page' => 'integer unsigned',
            'show_title' => 'boolean',
            'per_page' => 'integer unsigned',
        );
    }

    public function cacheVaryBy()
    {
        return array(
            'pageId' => Yii::app()->page->model->id,
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

    public static function getTree($id, $params, $recursive=0, $start=false)
    {
        if ($start)
            $items = Page::model()->order()->real()->selectPage($params['content']->pageNumber, $params['content']->per_page)->childrenPages($id)->localized()->getAll();
        else
            $items = Page::model()->order()->real()->childrenPages($id)->localized()->getAll();
        if ($recursive > 1) {
            foreach ($items as $k => $item)
            {
                $items[$k]['children'] = self::getTree($item['id'], $params, $recursive-1);
            }
        }
        return $items;
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

class UnitSitemapWidget extends ContentWidget
{
    public function init()
    {
        parent::init();
        $model = $this->params['content']->page ? Page::model()->findByPk($this->params['content']->page) : $this->params['page'];
        $this->params['title'] = $this->params['unit']->title ? $this->params['unit']->title : $model->title;

        $id = $this->params['content']->recursive ? $model->id : $model->parent_id;
        $this->params['items'] = array();
        if ($id)
            $this->params['items'] = UnitSitemap::getTree($id, $this->params, $this->params['content']->recursive, true);

        $this->params['count_items'] = count($this->params['items']);

        $this->params['pager'] = $this->params['content']->renderPager(
                $this->params['count_items'],
                $model->childrenCount,
                $this->params['content']->pageNumber,
                $this->params['content']->per_page
        );
        
    }
}