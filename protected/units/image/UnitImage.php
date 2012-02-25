<?php

class UnitImage extends ContentUnit
{
    public function name($language=null)
    {
        return Yii::t('UnitImage.main', 'Image', array(), null, $language);
    }
    
    public function icon()
    {
        return '/images/icons/fatcow/16x16/image.png';
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
            'WidgetImage'
        );
    }
    
    // Список моделей имеющихся в юните
    public function models()
    {
        return array(
            'ModelImage',
        );
    }

    public function settings()
    {
        return array_merge(parent::settings(__CLASS__), array(
            'show_border' => array(
                'type'=>'checkbox',
                'label'=>Yii::t('UnitImage.main', 'Show border'),
            )
        ));
    }
    public function settingsRules()
    {
        return array_merge(parent::settingsRules(), array(
            array('show_border', 'boolean')
        ));
    }

    
}