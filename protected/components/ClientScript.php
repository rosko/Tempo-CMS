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
            if (!Yii::app()->user->isGuest) {
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
        return strtr($str, array(
            '{core}' => $this->getCoreScriptUrl(),
            '{juiThemeUrl}' => Yii::app()->params->jui['themeUrl'],
            '{juiTheme}' => Yii::app()->params->jui['theme'],
        ));
    }
}