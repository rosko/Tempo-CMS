<?php
class Controller extends CController
{
	public $layout='//layouts/main';

    public function init()
    {
        if (!is_file(Yii::getPathOfAlias('config.general').'.php')) {
            
            Yii::app()->end();
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

        Unit::loadTypes();
    }

    public function setTheme()
    {
        $theme = Yii::app()->themeManager->themeNames[0];
        if (Yii::app()->settings->getValue('theme')
            && in_array(Yii::app()->settings->getValue('theme'), Yii::app()->themeManager->themeNames))
            $theme = Yii::app()->settings->getValue('theme');
        if (isset($this->_model)) {
            if ($this->_model->theme
                && in_array($this->_model->theme, Yii::app()->themeManager->themeNames))
                $theme = $this->_model->theme;
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

        if (isset($this->_model)) {
            $vars['title'] = $this->_model->title;
            $vars['keywords'] = $this->_model->keywords;
            $vars['description'] = $this->_model->description;
        }

        if ($vars['sitename']) {
    		$vars['title'] .=  ' - ' . $vars['sitename'];
        }
        $vars['themeBaseUrl'] = Yii::app()->theme->getBaseUrl();
        $vars['cssUrl'] = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.css'));
        $vars['jsUrl'] = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.js'));
        if (isset(Yii::app()->controller->_model))
            $vars['page'] = Yii::app()->controller->loadModel();
        $vars['editMode'] = Yii::app()->user->checkAccess('updatePage', array('page'=>$vars['page']));
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

            if($return)
                return $output;
            else
                echo $output;
        }
    }

}