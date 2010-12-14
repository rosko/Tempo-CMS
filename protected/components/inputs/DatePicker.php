<?php

Yii::import('zii.widgets.jui.CJuiDatePicker');

class DatePicker extends CJuiDatePicker
{
    public function init()
    {
        $this->themeUrl = Yii::app()->params['juiThemeUrl'];
        $this->theme = Yii::app()->params['juiTheme'];
        $this->language = Yii::app()->language;

        parent::init();
    }

}
