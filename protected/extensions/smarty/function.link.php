<?php
/**
 * Allows to generate links using CHtml::link().
 *
 * Syntax:
 * {link text="test"}
 * {link text="test" url="controller/action?param=value"}
 * {link text="test" url="/absolute/url"}
 * {link text="test" url="http://host/absolute/url"}
 *
 * @see CHtml::link().
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 */
function smarty_function_link($params, &$smarty){
    $text = empty($params['text']) ? '#' : $params['text'];
    $options = empty($params['options']) ? array() : $params['options'];    
    $url = '';

    if(!empty($params['url'])){
        $parts = parse_url($params['url']);
        if(!isset($parts['host']) && $parts['path'][1]!='/'){
            $par = array();
            parse_str($parts['query'], $par);
            $url = array_merge(
                array($parts['path']),
                $par
            );
        }
        else {
            $url = $params['url'];
        }        
    }     
    if (is_array($url))
        $url = Yii::app()->controller->createAbsoluteUrl($url[0],array_slice($url,1));
    if (!empty($params['params'])) {
        $url .= ((strpos($url,'?')===false) ? '?' : '&') . $params['params'];
    }
    if (empty($params['text']))
        return $url;
    else
        return CHtml::link($text, $url, $options);
}