<?php

Yii::import('zii.widgets.jui.CJuiDatePicker');

class DatePicker extends CJuiDatePicker
{
    public function run()
    {
        parent::run();
        $cs = Yii::app()->getClientScript();
        $cs->setCoreScriptUrl('/js/empty');       
    }
}

?>