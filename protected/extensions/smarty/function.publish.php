<?php

function smarty_function_publish($params, &$smarty){
    if(empty($params['file']))
        throw new CException(Yii::t('ESmartyViewRenderer.messages', 'Function "{name}" parameter should be specified.', array('{name}'=>'file')));

    $dir =  dirname(dirname($smarty->template_filepath)) . DIRECTORY_SEPARATOR .  'files';
    $filename = $dir . DIRECTORY_SEPARATOR . $params['file'];
    if (is_file($filename)) {
        return Yii::app()->getAssetManager()->publish($filename);
    }
}