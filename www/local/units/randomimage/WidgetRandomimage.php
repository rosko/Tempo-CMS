<?php


class WidgetRandomimage extends ContentWidget
{
    public function name($language=null)
    {
        return Yii::t('UnitRandomimage.main', 'Random image', array(), null, $language);
    }
    
    public function icon()
    {
        return '/images/icons/fatcow/16x16/image.png';
    }

    public function modelClassName()
    {
        return 'ModelRandomimage';
    }

    public function unitClassName()
    {
        return 'UnitRandomimage';
    }

    public function cacheable()
    {
        return false;
    }
    
    public function init()
    {
        parent::init();
        $this->params['image'] = $this->params['content']->images[rand(0,count($this->params['content']->images)-1)];
    }
}