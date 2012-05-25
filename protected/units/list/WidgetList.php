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
        if (Yii::$classMap[$this->params['content']->class_name] &&
                method_exists($this->params['content']->class_name, 'feedItem')) {
            $rule = $this->params['content']->makeRule();
            $modelClass = $this->params['content']->class_name;
            $feed = call_user_func(array($this->params['content']->class_name, 'feedItem'));
            eval("\$items = {$modelClass}::model()->{$rule}findAll();");
            foreach ($items as $itemSource)
            {
                $item = $itemSource->attributes;
                foreach ($feed as $element => $attribute)
                {
                    if ($attribute)
                        $item[$element] = $itemSource->{$attribute};
                }

                $this->params['items'][] = $item;
            }
        }        
    }
}
