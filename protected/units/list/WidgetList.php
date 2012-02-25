<?php

class WidgetList extends ContentWidget
{
	public function name($language=null)
    {
        return Yii::t('UnitList.main', 'List', array(), null, $language);
    }

    public function icon()
    {
        return '/images/icons/fatcow/16x16/newspaper_link.png';
    }

    public function modelClassName()
    {
        return 'ModelList';
    }

    public function unitClassName()
    {
        return 'UnitList';
    }

    public function init()
    {
        parent::init();
        $this->params['items'] = array();
        // Сейчас функция нацелена на виджеты, но нужно переделать на модели.
        // Правда, тогда не ясно в каком виде выводить полученную информацию.
        // Где взять шаблоны?
        if (Yii::$classMap[$this->params['content']->class_name]) {
            $rule = $this->params['content']->makeRule();
            $modelClass = call_user_func(array($this->params['content']->class_name, 'modelClassName'));
            eval("\$items = {$modelClass}::model()->{$rule}findAll();");
            foreach ($items as $item)
            {
                $this->params['items'][] = $item->widget($this->params['content']->class_name, array(), true);
            }
        }        
    }
}
