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
        $keys = array_keys($this->params['content']->images);
        $image = $this->params['content']->images[$keys[rand(0,count($keys)-1)]];
        $image['filename'] = ImageHelper::resizeDown(
            $image['filename'],
            $this->params['content']->width,
            $this->params['content']->height
        );
        $this->params['image'] = $image;
        if (isset($image['data'][Yii::app()->language.'_caption'])) {
            $this->params['caption'] = $image['data'][Yii::app()->language.'_caption'];
        } else {
            $this->params['caption'] = $this->params['widget']->title;
        }
    }
}