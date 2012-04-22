<?php
class Controller extends CController
{
	public $layout='//layouts/main';

    public function init()
    {
        if (!is_file(Yii::getPathOfAlias('config.general').'.php')) {
            // ToDo: Сделать вызов инсталлятора
            Yii::app()->end();
        }

        ini_set('mbstring.internal_encoding', 'UTF-8');
        ini_set('mbstring.func_overload', 2);

        if (Yii::app()->settings->getValue('timezone')) {
            $timezone = Yii::app()->settings->getValue('timezone');
            if (Yii::app()->user->data && Yii::app()->user->data->timezone) {
                $timezone = Yii::app()->user->data->timezone;
            }
            
        	date_default_timezone_set($timezone);
    		$tz = date("Z");
            $tz = sprintf('%s%02d:%02d', ($tz < 0 ? '-' : '+'), abs($tz / 3600), abs($tz % 3600) / 60);
            Yii::app()->db->createCommand('set @@session.time_zone = "' . $tz . '";')->execute();            
        }
        
        $language = 'en';
        $langs = array_keys(I18nActiveRecord::getLangs());
        if (Yii::app()->request->preferredLanguage && in_array(Yii::app()->request->preferredLanguage, $langs))
            $language = Yii::app()->request->preferredLanguage;

        if (Yii::app()->settings->getValue('language'))
            $language = Yii::app()->settings->getValue('language');

        if (isset($_REQUEST['language']) && in_array($_REQUEST['language'], $langs))
            $language = $_REQUEST['language'];

        if (!in_array($language, $langs)) {
            $language = $langs[0];
        }
        Yii::app()->language = $language;

        if (!Yii::app()->request->isAjaxRequest)
            Yii::app()->getClientScript()->registerCssFile(Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.css')).'/icons/'.Yii::app()->params['icons'].'.css');

        ContentUnit::loadUnits();

        Yii::app()->getClientScript()->packages = require(Yii::getPathOfAlias('application.config').'/packages.php');
    }

    public function setTheme()
    {
        $theme = Yii::app()->themeManager->themeNames[0];
        if (Yii::app()->settings->getValue('theme')
            && in_array(Yii::app()->settings->getValue('theme'), Yii::app()->themeManager->themeNames))
            $theme = Yii::app()->settings->getValue('theme');
        if (Yii::app()->page->model) {
            if (Yii::app()->page->model->theme
                && in_array(Yii::app()->page->model->theme, Yii::app()->themeManager->themeNames))
                $theme = Yii::app()->page->model->theme;
        }
        Yii::app()->theme = $theme;
    }

    public function render($view, $data=null, $return = false)
    {
        if (!Yii::app()->theme)
            $this->setTheme();

        $vars = array();
        $vars['title'] = $this->pageTitle;
    	$vars['sitename'] = Yii::app()->settings->getValue('sitename');

        if (Yii::app()->page->model) {
            $vars['title'] = Yii::app()->page->model->title;
            $vars['keywords'] = Yii::app()->page->model->keywords;
            $vars['description'] = Yii::app()->page->model->description;
        }

        if ($vars['sitename']) {
    		$vars['title'] .=  ' - ' . $vars['sitename'];
        }
        $vars['themeBaseUrl'] = Yii::app()->theme->getBaseUrl();
        $vars['cssUrl'] = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.css'));
        $vars['jsUrl'] = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.js'));
        $vars['page'] = Yii::app()->page->model;
        $vars['editMode'] = !Yii::app()->user->isGuest;
        $vars['settings']['global'] = Yii::app()->settings->model->getAttributes();


        if($this->beforeRender($view))
        {
            $output=$this->renderPartial($view,$data,true);
            if(($layoutFile=$this->getLayoutFile($this->layout))!==false && !Yii::app()->request->isAjaxRequest) {
                $vars['content'] = $output;
                $output=$this->renderFile($layoutFile,$vars,true);
            }

            $this->afterRender($view,$output);

            $output=$this->processOutput($output);

            $output = preg_replace("/(\<input[^>]*value=[\'\"]?)([^\"\']*)([\'\"]?[^>]*name=[\'\"]?".Yii::app()->getRequest()->csrfTokenName."[\'\"]?[^>]*\>)/msi", "\${1}".Yii::app()->getRequest()->getCsrfToken()."\${3}", $output);

            if($return)
                return $output;
            else
                echo $output;
        }
    }

	public function putDynamic($callback, $params)
	{
        echo '<!-- dynamic '.base64_encode(serialize(array($callback,$params))).' -->';
	}

    public function processOutput($output)
    {
        return $this->processDynamic(parent::processOutput($output));
    }

    public function processDynamic($output)
    {
        $output=preg_replace_callback("/<!--\sdynamic\s([^\s]*)\s-->/m", array($this,'replaceDynamic'), $output);
        return $output;
    }

    protected function replaceDynamic($matches)
    {
        list($callback,$params)=unserialize(base64_decode($matches[1], true));
        return call_user_func($callback, $params);
    }

	/**
     * Исполняет проверку формы
     *
     * @param CActiveRecord $model
     * @param array $attributes
     * @param boolean $loadInput
     */
    protected function performAjaxValidation($model, $attributes=null, $loadInput=true)
	{
		if(isset($_REQUEST['ajax-validate']))
		{
            echo CActiveForm::validate($model, $attributes, $loadInput);
			Yii::app()->end();
		}
	}

}