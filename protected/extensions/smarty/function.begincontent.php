<?php

function smarty_function_begincontent($params, &$smarty){
    if(empty($params['name']))
        throw new CException(Yii::t('ESmartyViewRenderer.messages', 'Function "{name}" parameter should be specified.', array('{name}'=>'name')));

    Yii::app()->controller->beginContent($params['name']);

}