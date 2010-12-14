<?php

function smarty_function_form($params, &$smarty)
{
    $htmlOptions = $params;
    unset($htmlOptions['action']);
    unset($htmlOptions['method']);
    return CHtml::form($params['action'], $params['method'], $htmlOptions);
}