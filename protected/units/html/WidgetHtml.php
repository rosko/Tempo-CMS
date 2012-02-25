<?php

class WidgetHtml extends ContentWidget
{
    public function name($language=null)
    {
        return Yii::t('UnitHtml.main', 'HTML', array(), null, $language);
    }
    
    public function icon()
    {
        return '/images/icons/fatcow/16x16/html.png';
    }
    
    public function modelClassName()
    {
        return 'ModelHtml';
    }

    public function unitClassName()
    {
        return 'UnitHtml';
    }

}