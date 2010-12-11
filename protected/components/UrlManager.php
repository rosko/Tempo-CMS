<?php

class UrlManager extends CUrlManager
{
    public $fullUrl = false;

	public function createUrl($route,$params=array(),$ampersand='&')
	{
        if (!isset($params['language'])) {
            if (Yii::app()->language != Yii::app()->settings->getValue('language'))
                $params['language'] = Yii::app()->language;
        } else {
            if ($params['language'] == Yii::app()->settings->getValue('language'))
                unset($params['language']);
        }

        if (!isset($params['alias']) && isset($params['id'])) {
            $params['alias'] = ''; 
        }

        if ($this->getUrlFormat()==self::GET_FORMAT) {
            unset($params['alias']);
            unset($params['url']);
        }

        if (!empty($params['url'])) {
            $params['url'] = substr($params['url'],1);
        }
        if ($this->fullUrl && !empty($params['url'])) {
            unset($params['alias']);
            unset($params['id']);
        } else {
            unset($params['url']);
        }

        $ret = parent::createUrl($route, $params, $ampersand);
        $ret = str_ireplace('%2F', '/', $ret);
        return $ret;
    }

}

