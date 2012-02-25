<?php

class WidgetLangswitcher extends ContentWidget
{
    public function name($language=null)
    {
        return Yii::t('UnitLangswitcher.main', 'Language switcher', array(), null, $language);
    }
    
    public function icon()
    {
        return '/images/icons/fatcow/16x16/style_go.png';
    }

    public function modelClassName()
    {
        return 'ModelLangswitcher';
    }

    public function unitClassName()
    {
        return 'UnitLangswitcher';
    }

    public function cacheVaryBy()
    {
        return array(
            'pageId' => Yii::app()->page->model->id,
            '_GET' => $_GET,
        );
    }

    public function init()
    {
        parent::init();
        $this->params['languages'] = I18nActiveRecord::getLangs();        
    }
}