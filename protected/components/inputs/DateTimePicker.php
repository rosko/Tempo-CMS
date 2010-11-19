<?php

Yii::import('application.extensions.timepicker.EJuiDateTimePicker');

class DateTimePicker extends EJuiDateTimePicker
{
    public function init()
    {
        $this->themeUrl = Yii::app()->params->jui['themeUrl'];
        $this->theme = Yii::app()->params->jui['theme'];
        $this->language = 'ru';

        $defaults = array(
            'dateFormat'=>'yy-mm-dd',
            'timeFormat'=>'hh:mm:ss',
            'stepMinute'=> 5,
            //'hourGrid'=>6,
            //'minuteGrid'=>15,

            'timeOnlyTitle' => 'Выберите время',
            'timeText' => 'Время',
            'hourText' => 'Часы',
            'minuteText' => 'Минуты',
            'secondText' => 'Секунды',
            'currentText' => 'Сейчас',
            'doneText' => 'Оk',
        );

        foreach ($defaults as $k => $v)
        {
            if (empty($this->options[$k]))
                $this->options[$k] = $v;
        }
        parent::init();
    }

}