<?php

function smarty_function_dateformat($params, &$smarty){
    if(empty($params['pattern']))
        $params['pattern'] = 'd MMMM yyyy';
    if(empty($params['time']))
        $params['time'] = time();

    return Yii::app()->dateFormatter->format(
        $params['pattern'],
        intval($params['time'])==$params['time'] ? $params['time'] : strtotime($params['time'])
    );

}