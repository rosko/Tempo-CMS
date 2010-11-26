<?php

function smarty_function_area($params, &$smarty){
    if(empty($params['name']))
        throw new CException("В функции area должен быть указан параметр name");

    return Yii::app()->controller->widget('Area', $params, true);

}