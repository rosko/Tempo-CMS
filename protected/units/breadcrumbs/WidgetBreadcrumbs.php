<?php

class WidgetBreadcrumbs extends ContentWidget
{
    const DEFAULT_SEPARATOR = ' &raquo; ';

    public function name($language=null)
    {
        return Yii::t('UnitBreadcrumbs.main', 'Breadcrumbs', array(), null, $language);
    }

    public function icon()
    {
        return '/images/icons/fatcow/16x16/hand_point.png';
    }

    public function modelClassName()
    {
        return 'ModelBreadcrumbs';
    }

    public function unitClassName()
    {
        return 'UnitBreadcrumbs';
    }

    public function cacheVaryBy()
    {
        return array(
            'pageId' => Yii::app()->page->model->id,
        );
    }

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