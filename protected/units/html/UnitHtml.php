<?php

class UnitHtml extends ContentUnit
{
    public function name($language=null)
    {
        return Yii::t('UnitHtml.main', 'HTML', array(), null, $language);
    }
    
    public function icon()
    {
        return '/images/icons/fatcow/16x16/html.png';
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
            'WidgetHtml'
        );
    }
    
    // Список моделей имеющихся в юните
    public function models()
    {
        return array(
            'ModelHtml',
        );
    }
    
}
