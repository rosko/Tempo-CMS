<?php

function smarty_function_accessdenied($params, &$smarty){
    echo '<h2 class="error">'.Yii::t('cms','Error').'</h2>';
    echo '<p>'.Yii::t('cms','Access denied').'</p>';
    $widgetLogin = ModelLogin::model()->find();
    if ($widgetLogin && $widgetLogin->widget_id) {
        $pageWidget = PageWidget::model()->find('widget_id = :widget_id', array(
            'widget_id'=>$widgetLogin->widget_id,
        ));
        $widgetLogin->run(array(
            'pageWidget'=>$pageWidget
        ));
    }

}