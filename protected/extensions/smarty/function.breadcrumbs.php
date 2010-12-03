<?php

function smarty_function_breadcrumbs($params, &$smarty){
//    if(empty($params['text'])){
//        throw new CException(Yii::t('ESmartyViewRenderer.messages', 'Function "{name}" parameter should be specified.', array('{name}'=>'text')));
//    }

    return Yii::app()->controller->widget('zii.widgets.CBreadcrumbs', array(
        'separator' => $params['separator'],
        'homeLink' => $params['homeLink'],
        'links'=> $params['links']
    ), true);

}