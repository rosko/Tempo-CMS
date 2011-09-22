<?php

class ClientScript extends CClientScript
{
    public $notLoadCoreScriptsOnAjax = array();

	public function registerCoreScript($name)
	{
        if (!Yii::app()->request->isAjaxRequest || !in_array($name, $this->notLoadCoreScriptsOnAjax))
            parent::registerCoreScript($name);
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