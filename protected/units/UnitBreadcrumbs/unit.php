<?php

class UnitBreadcrumbs extends Content
{
	const ICON = '/images/icons/fatcow/16x16/hand_point.png';
    const HIDDEN = true;

    const DEFAULT_SEPARATOR = ' &raquo; ';

    public function unitName($language=null)
    {
        return Yii::t('UnitBreadcrumbs.unit', 'Breadcrumbs', array(), null, $language);
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
			array('unit_id', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
			array('separator', 'length', 'max'=>16, 'encoding'=>'UTF-8'),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
            'separator'=> Yii::t('UnitBreadcrumbs.unit', 'Separator'),
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
                'separator'=>array(
                    'type'=>'text'
                ),
                Yii::t('UnitBreadcrumbs.unit', 'If empty, use \'<b>{separator}</b>\'', array('{separator}'=>UnitBreadcrumbs::DEFAULT_SEPARATOR)),
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
            'page_id' => Yii::app()->controller->loadModel()->id,
        );
    }

    public function  cacheDependencies() {
        $ids = str_replace('0,','',Yii::app()->controller->loadModel()->path) . ',' . Yii::app()->controller->loadModel()->id;
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
            '{breadcrumbs separator=$separator homeLink=$homeLink  links=$links}' => Yii::t('UnitBreadcrumbs.unit', 'Breadcrumbs'),
            '{$separator}' => Yii::t('UnitBreadcrumbs.unit', 'Separator'),
            '{$homeLink}' => Yii::t('UnitBreadcrumbs.unit', 'Caption or link for homepage'),
            '{$links}' => Yii::t('UnitBreadcrumbs.unit', 'Links'),
        );
    }

    public function prepare($params)
    {
        $params = parent::prepare($params);

        $ids = explode(',', $params['page']->path);
        $pages = Page::model()->findAll(array(
            'condition' => '`id` IN ('.$params['page']->path.')',
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
            $links[$parents[$id]->title] = array('page/view', 'id'=>$parents[$id]->id, 'alias'=>$parents[$id]->alias, 'url'=>$parents[$id]->url);
        }
        if ($params['page']->id != 1)
            $links[] = $params['page']->title;
        else
            $links[] = '';
        $params['links'] = $links;

        $params['separator'] = $params['content']->separator ? $params['content']->separator : self::DEFAULT_SEPARATOR;

        $params['homeLink'] = ($parents ? CHtml::link($parents[1]->title, array('page/view', 'id'=>$parents[1]->id, 'alias'=>$parents[1]->alias, 'url'=>$parents[1]->url)) : $params['page']->title);

        return $params;
    }
}