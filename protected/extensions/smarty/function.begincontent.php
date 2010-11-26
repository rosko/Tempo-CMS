<?php

function smarty_function_begincontent($params, &$smarty){
    if(empty($params['name']))
        throw new CException("В функции begincontent должен быть указан параметр name");

    Yii::app()->controller->beginContent($params['name']);

}