<?php

$tabs = array();

$classNames = CMap::mergeArray(Yii::app()->params['coreModels'], array_keys(ContentModel::getInstalledModels(true)));

foreach ($classNames as $className) {

    $title = method_exists($className, 'modelName') ? call_user_func(array($className, 'modelName')) : $className;

    $tmp = $this->widget('AccessRights',
        array(
             'name' => 'Rights['.$className.']',
             'objects' => array($className),
        ), true
    );
    if ($tmp) {
        $tabs[$title] = $tmp;
    }

}

$this->widget('zii.widgets.jui.CJuiTabs',
    array(
        'tabs' => $tabs,
    )
);
