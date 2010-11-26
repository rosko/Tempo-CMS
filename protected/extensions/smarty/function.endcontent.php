<?php

function smarty_function_endcontent($params, &$smarty){
    Yii::app()->controller->endContent();
}