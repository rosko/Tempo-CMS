<?php

function smarty_function_breadcrumbs($params, &$smarty){
//    if(empty($params['text'])){
//        throw new CException("Function 'text' parameter should be specified.");
//    }

    return Yii::app()->controller->widget('zii.widgets.CBreadcrumbs', array(
        'separator' => $params['separator'],
        'homeLink' => $params['homeLink'],
        'links'=> $params['links']
    ), true);

}