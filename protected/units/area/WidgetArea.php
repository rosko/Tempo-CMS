<?php

class WidgetArea extends ContentWidget 
{
    public function name($language=null)
    {
        return Yii::t('UnitArea.main', 'Area of blocks', array(), null, $language);
    }
    
    public function modelClassName()
    {
        return 'ModelArea';
    }

    public function unitClassName()
    {
        return 'UnitArea';
    }

    public function icon()
    {
        return '/images/icons/fatcow/16x16/html.png';
    }

    public function init()
    {
        parent::init();
        $this->params['areaId'] = 'widget'.$this->params['widget']->id.'UnitArea_items';
        $this->params['pageWidgets'] = PageWidget::model()->findAll(array(
            'condition' => '`area` = :area',
            'params' => array(
                'area' => $this->params['areaId']
            ),
            'with' => array('widget'),
            'order' => '`order`'
            ));
    }
    
}