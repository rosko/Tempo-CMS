<?php

Yii::import('application.extensions.timepicker.EJuiDateTimePicker');

class DateTimePicker extends EJuiDateTimePicker
{
    public function init()
    {
        $this->themeUrl = Yii::app()->params->jui['themeUrl'];
        $this->theme = Yii::app()->params->jui['theme'];
        $this->language = Yii::app()->language;

        $defaults = array(
            'dateFormat'=>'yy-mm-dd',
            'timeFormat'=>'hh:mm:ss',
            'stepMinute'=> 5,
            //'hourGrid'=>6,
            //'minuteGrid'=>15,

            'timeOnlyTitle' => Yii::t('cms', 'Choose time'),
            'timeText' => Yii::t('cms', 'Time'),
            'hourText' => Yii::t('cms', 'Hours'),
            'minuteText' => Yii::t('cms', 'Minutes'),
            'secondText' => Yii::t('cms', 'Seconds'),
            'currentText' => Yii::t('cms', 'Now'),
            'doneText' => Yii::t('cms', 'Ok'),
        );

        foreach ($defaults as $k => $v)
        {
            if (empty($this->options[$k]))
                $this->options[$k] = $v;
        }
        parent::init();
    }

}