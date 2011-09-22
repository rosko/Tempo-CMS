<?php

function smarty_function_accessdenied($params, &$smarty){
    echo '<h2 class="error">'.Yii::t('cms','Error').'</h2>';
    echo '<p>'.Yii::t('cms','Access denied').'</p>';
    $unitLogin = UnitLogin::model()->find();
    if ($unitLogin && $unitLogin->unit_id) {
        $pageUnit = PageUnit::model()->find('unit_id = :unit_id', array(
            'unit_id'=>$unitLogin->unit_id,
        ));
        $unitLogin->run(array(
            'pageUnit'=>$pageUnit
        ));
    }

}