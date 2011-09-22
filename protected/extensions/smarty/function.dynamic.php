<?php

function smarty_function_dynamic($params, &$smarty){
    if(empty($params['callback']))
        throw new CException(Yii::t('ESmartyViewRenderer.messages', 'Function "{name}" parameter should be specified.', array('{name}'=>'callback')));

    $callback = $params['callback'];
    unset($params['callback']);
    $route = Yii::app()->getController()->getId() . '/' . Yii::app()->getController()->getAction()->getId();
    if ($route == 'view/index') {
        Yii::app()->controller->putDynamic($callback,$params);
    } else {
        echo call_user_func_array($callback, $params);
    }    
}