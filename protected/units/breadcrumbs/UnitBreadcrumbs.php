<?php

class UnitBreadcrumbs extends ContentUnit
{
    public function name($language=null)
    {
        return Yii::t('UnitBreadcrumbs.main', 'Breadcrumbs', array(), null, $language);
    }

    public function icon()
    {
        return '/images/icons/fatcow/16x16/hand_point.png';
    }
    
    // Версия в виде n.YYYYMMDD[HH[MM]] - последние части (часы, минуты) - необязательно
    public function version()
    {
        return 1.20120224;
    }
    
    // Список виджетов имеющихся в юните
    public function widgets()
    {
        return array(
            'WidgetBreadcrumbs'
        );
    }
    
    // Список моделей имеющихся в юните
    public function models()
    {
        return array(
            'ModelBreadcrumbs',
        );
    }
    
}
