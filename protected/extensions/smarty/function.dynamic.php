<?php

function smarty_function_dynamic($params, &$smarty){
    if(empty($params['callback']))
        throw new CException(Yii::t('ESmartyViewRenderer.messages', 'Function "{name}" parameter should be specified.', array('{name}'=>'callback')));

    $callback = $params['callback'];
    unset($params['callback']);
    Yii::app()->controller->renderDynamic($callback,$params);

}