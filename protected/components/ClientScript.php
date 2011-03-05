<?php

class ClientScript extends CClientScript
{
    public $notLoadCoreScriptsOnAjax = array();
    public $neededCoreScripts = array();
    public $neededScriptFiles = array();
    public $neededCssFiles = array();
    public $neededAdminCoreScripts = array();
    public $neededAdminScriptFiles = array();
    public $neededAdminCssFiles = array();

	public function registerCoreScript($name)
	{
        if (!Yii::app()->request->isAjaxRequest || !in_array($name, $this->notLoadCoreScriptsOnAjax))
            parent::registerCoreScript($name);
    }

	public function render(&$output)
	{
        if (!Yii::app()->request->isAjaxRequest) {
            foreach ($this->neededCoreScripts as $name)
                $this->registerCoreScript($name);
            foreach ($this->neededScriptFiles as $url)
                $this->registerScriptFile($this->strtr($url));
            foreach ($this->neededCssFiles as $url)
                $this->registerCssFile($this->strtr($url));
            if (Yii::app()->user->checkAccess('createPage') || 
                Yii::app()->user->checkAccess('updatePage') ||
                Yii::app()->user->checkAccess('deletePage')) {
                foreach ($this->neededAdminCoreScripts as $name)
                    $this->registerCoreScript($name);
                foreach ($this->neededAdminScriptFiles as $url)
                    $this->registerScriptFile($this->strtr($url));
                foreach ($this->neededAdminCssFiles as $url)
                    $this->registerCssFile($this->strtr($url));
            }
        }
        parent::render($output);
    }

    private function strtr($str)
    {
        if (strpos($str, '{jnotify}')!==false) {
            $jnotifyPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.vendors.jnotify'));
        }
        if (strpos($str, '{fancybox}')!==false) {
            $fancyboxPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.vendors.fancybox'));
        }
        if (strpos($str, '{topbox}')!==false) {
            $topboxPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.vendors.topbox'));
        }
        if (strpos($str, '{js}')!==false) {
            $jsPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.js'),false,-1,true);
        }
        if (strpos($str, '{css}')!==false) {
            $cssPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.css'));
        }
        if (strpos($str, '{juiThemeUrl}')!==false) {
            $juiThemeUrl=Yii::app()->params['juiThemeUrl'] = Yii::app()->params['juiThemeUrl'] ? Yii::app()->params['juiThemeUrl'] : Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.css.jui'));
        }
        return strtr($str, array(
            '{core}' => $this->getCoreScriptUrl(),
            '{juiThemeUrl}' => $juiThemeUrl,
            '{juiTheme}' => Yii::app()->params['juiTheme'],
            '{jsI18N}' => '/?r=page/jsI18N&language='.Yii::app()->language,
            '{jnotify}' => $jnotifyPath,
            '{fancybox}' => $fancyboxPath,
            '{topbox}' => $topboxPath,
            '{js}' => $jsPath,
            '{css}' => $cssPath,
        ));
    }
	public function registerCssFile($url,$media='')
	{
        if (!Yii::app()->request->isAjaxRequest) {
            return parent::registerCssFile($url, $media);
        } else {
            echo '<link rel="stylesheet" type="text/css" href="'.CHtml::encode($url).'" media="'.$media.'" />';
            return $this;
        }
    }

    public function registerScriptFile($url,$position=self::POS_HEAD)
	{
        if (!Yii::app()->request->isAjaxRequest) {
            return parent::registerScriptFile($url, $position);
        } else {
            echo '<script type="text/javascript" src="'.CHtml::encode($url).'"></script>';
            return $this;
        }
    }
}