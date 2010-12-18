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

        if (isset($params['id']) && $params['id']==1) {
            return Yii::app()->homeUrl;
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
            $tmp = explode('/',$params['url']);
            if ($tmp[count($tmp)-1] == '') {
                unset($params['url']);
            }
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

	public function parsePathInfo($pathInfo)
	{
        parent::parsePathInfo($pathInfo);
        if (isset($_GET['language'])) {
            $langs = I18nActiveRecord::getLangs();
            if (!isset($langs[$_GET['language']])) {
                if (isset($_GET['url'])) {
                    $_REQUEST['url']=$_GET['url'] = $_GET['language'] . '/' . $_GET['url'];
                } elseif (isset($_GET['alias'])) {
                    $_REQUEST['url']=$_GET['url'] = $_GET['language'] . '/' . $_GET['alias'];
                    unset($_GET['alias']);
                    unset($_REQUEST['alias']);
                } else {
                    if ($this->fullUrl)
                        $_REQUEST['url']=$_GET['url'] = $_GET['language'];
                    else
                        $_REQUEST['alias']=$_GET['alias'] = $_GET['language'];
                }
                unset($_GET['language']);
                unset($_REQUEST['language']);
            }
        }        
    }
}

