<?php

class UnitMenu extends ContentUnit
{
    public function name($language=null)
    {
        return Yii::t('UnitMenu.main', 'Menu', array(), null, $language);
    }
    
    public function icon()
    {
        return '/images/icons/fatcow/16x16/breeze.png';
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
            'WidgetMenu'
        );
    }
    
    // Список моделей имеющихся в юните
    public function models()
    {
        return array(
            'ModelMenu',
        );
    }
    
}
