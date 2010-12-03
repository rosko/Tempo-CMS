<?php

function smarty_function_area($params, &$smarty){
    if(empty($params['name']))
        throw new CException(Yii::t('ESmartyViewRenderer.messages', 'Function "{name}" parameter should be specified.', array('{name}'=>'name')));

    return Yii::app()->controller->widget('Area', $params, true);

}