<?php

class WidgetHeader extends ContentWidget
{
    public function name($language=null)
    {
        return Yii::t('UnitHeader.main', 'Header', array(), null, $language);
    }

    public function icon()
    {
        return '/images/icons/fatcow/16x16/text_heading_1.png';
    }
    
    public function modelClassName()
    {
        return 'ModelHeader';
    }

    public function unitClassName()
    {
        return 'UnitHeader';
    }

    public function init()
    {
        parent::init();
        if ($this->params['widget']->title == '') {
            $this->params['widget']->title = '&nbsp;';
        }
        
    }
}