<?php

Yii::import('zii.widgets.jui.CJuiDatePicker');

class DatePicker extends CJuiDatePicker
{
    public function init()
    {
        $this->themeUrl = Yii::app()->params->jui['themeUrl'];
        $this->theme = Yii::app()->params->jui['theme'];
        $this->language = 'ru';

        parent::init();
    }

}

?>