<?php

function smarty_function_redirect($params, &$smarty){
    if(empty($params['to']))
        throw new CException(Yii::t('ESmartyViewRenderer.messages', 'Function "{name}" parameter should be specified.', array('{name}'=>'to')));

    header('Location: '. $params['to']);
}