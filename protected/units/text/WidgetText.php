<?php

class WidgetText extends ContentWidget
{
    public function modelClassName()
    {
        return 'ModelText';
    }

    public function unitClassName()
    {
        return 'UnitText';
    }

    public function name($language=null)
    {
        return Yii::t('UnitText.main', 'Text', array(), null, $language);
    }
    
    public function icon()
    {
        return '/images/icons/fatcow/16x16/text_dropcaps.png';
    }
}