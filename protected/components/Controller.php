<?php
class Controller extends CController
{
	public $layout='//layouts/main';

    public function init()
    {
        if (!is_file(Yii::getPathOfAlias('application.config.config').'.php')) {
            
            Yii::app()->end();
        }

        $language = 'en';
        if (Yii::app()->settings->getValue('language'))
            $language = Yii::app()->settings->getValue('language');

        if (isset($_REQUEST['language']) && in_array($_REQUEST['language'], array_keys(I18nActiveRecord::getLangs())))
            $language = $_REQUEST['language'];

        Yii::app()->language = $language;

        Unit::loadTypes();
    }

    public function render($view, $data=null, $return = false)
    {
        $theme = Yii::app()->themeManager->themeNames[0];
        if (Yii::app()->settings->getValue('theme') 
            && in_array(Yii::app()->settings->getValue('theme'), Yii::app()->themeManager->themeNames))
            $theme = Yii::app()->settings->getValue('theme');

        $vars = array();
        $vars['title'] = $this->pageTitle;
    	$vars['sitename'] = Yii::app()->settings->getValue('sitename');

        if (isset($this->_model)) {
            $vars['title'] = $this->_model->title;
            $vars['keywords'] = $this->_model->keywords;
            $vars['description'] = $this->_model->description;
            if ($this->_model->theme 
                && in_array($this->_model->theme, Yii::app()->themeManager->themeNames))
                $theme = $this->_model->theme;
        }
        Yii::app()->theme = $theme;

        if ($vars['sitename']) {
    		$vars['title'] .=  ' - ' . $vars['sitename'];
        }
        $vars['themeBaseUrl'] = Yii::app()->theme->getBaseUrl();
        if (isset(Yii::app()->controller->_model))
            $vars['page'] = Yii::app()->controller->loadModel();
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

            if($return)
                return $output;
            else
                echo $output;
        }
    }

}