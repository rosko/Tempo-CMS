<?php

function smarty_function_gridview($params, &$smarty){
    return Yii::app()->controller->widget('zii.widgets.grid.CGridView', $params, true);
}