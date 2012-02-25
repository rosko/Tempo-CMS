<?php

class WidgetSearch extends ContentWidget
{
    public function name($language=null)
    {
        return Yii::t('UnitSearch.main', 'Search', array(), null, $language);
    }
    
    public function icon()
    {
        return '/images/icons/fatcow/16x16/find.png';
    }

    public function modelClassName()
    {
        return 'ModelSearch';
    }

    public function unitClassName()
    {
        return 'UnitSearch';
    }

    public function init()
    {
        parent::init();
        $this->params['q'] = $_GET['q'];        
    }
}