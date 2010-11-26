<?php

function smarty_function_registercss($params, &$smarty){
    if(empty($params['file']))
        throw new CException("В функции registercss должен быть указан параметр file");

    if((Yii::app()->getViewRenderer())!==null)
        $extension=Yii::app()->getViewRenderer()->fileExtension;
    else
        $extension='.php';

    $dir =  dirname(dirname($smarty->template_filepath)) . DIRECTORY_SEPARATOR .  'files';
    $filename = $dir . DIRECTORY_SEPARATOR . $params['file'];

    if (is_file($filename)) {
        $baseUrl = Yii::app()->getAssetManager()->publish($filename);
        $cs=Yii::app()->getClientScript();
        $cs->registerCssFile($baseUrl);
    }
}