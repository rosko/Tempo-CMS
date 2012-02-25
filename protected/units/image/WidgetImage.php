<?php

class WidgetImage extends ContentWidget
{
    public function name($language=null)
    {
        return Yii::t('UnitImage.main', 'Image', array(), null, $language);
    }
    
    public function modelClassName()
    {
        return 'ModelImage';
    }

    public function unitClassName()
    {
        return 'UnitImage';
    }

    public function icon()
    {
        return '/images/icons/fatcow/16x16/image.png';
    }

}