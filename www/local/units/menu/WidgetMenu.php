<?php

class WidgetMenu extends ContentWidget
{
    public function name($language=null)
    {
        return Yii::t('UnitMenu.main', 'Menu', array(), null, $language);
    }
    
    public function icon()
    {
        return '/images/icons/fatcow/16x16/breeze.png';
    }
    
    public function modelClassName()
    {
        return 'ModelMenu';
    }

    public function unitClassName()
    {
        return 'UnitMenu';
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
        $this->params['tree'] = Page::model()->getTree();        
    }
}