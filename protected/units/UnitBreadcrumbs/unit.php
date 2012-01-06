<?php

class UnitBreadcrumbs extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/hand_point.png';
    }
    
    public function hidden()
    {
        return true;
    }
    
    public function unitName($language=null)
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
                Yii::t('UnitBreadcrumbs.main', 'If empty, use \'<b>{separator}</b>\'', array('{separator}'=>UnitBreadcrumbs::DEFAULT_SEPARATOR)),
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
            'separator' => 'char(32)',
        );
    }

    public function cacheVaryBy()
    {
        return array(
            'pageId' => Yii::app()->page->model->id,
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

class UnitBreadcrumbsWidget extends ContentWidget
{
    const DEFAULT_SEPARATOR = ' &raquo; ';

    public function init()
    {
        parent::init();
        $ids = explode(',', $this->params['page']->path);
        $pages = Page::model()->findAll(array(
            'condition' => '`id` IN ('.$this->params['page']->path.')',
            'order' => '`path` DESC'
        ));
        $parents = array();
        foreach ($pages as $p) {
            $parents[$p->id] = $p;
        }
        unset($pages);

        $links = array();
        foreach ($ids as $id) {
            if ($id == 0 || $id == 1) continue;
            $links[$parents[$id]->title] = array('view/index', 'pageId'=>$parents[$id]->id, 'alias'=>$parents[$id]->alias, 'url'=>$parents[$id]->url);
        }
        if ($this->params['page']->id != 1)
            $links[] = $this->params['page']->title;
        else
            $links[] = '';
        $this->params['links'] = $links;

        $this->params['separator'] = $this->params['content']->separator ? $this->params['content']->separator : self::DEFAULT_SEPARATOR;

        $this->params['homeLink'] = ($parents ? CHtml::link($parents[1]->title, array('view/index', 'pageId'=>$parents[1]->id, 'alias'=>$parents[1]->alias, 'url'=>$parents[1]->url)) : $this->params['page']->title);
        
    }    
}