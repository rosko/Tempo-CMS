<?php

class ContentWidget extends CWidget
{
    public $params;
    public $content;

    public function init()
    {
        $this->params['className'] = get_class($this->content);
        $this->params['unit'] = $this->content->unit;
        $this->params['content'] = $this->content;
        $this->params['isGuest'] = Yii::app()->user->isGuest;
        $this->params['language'] = Yii::app()->language;
        $get = $_GET;
        unset($get['alias'],$get['pageId'],$get['url'],$get['language']);
        $this->params['getParams'] = http_build_query($get);
        if (!$this->params['isGuest']) {
            $this->params['user'] = User::model()->findByPk(Yii::app()->user->id);
        }
        $this->params['page'] = Yii::app()->page->model;
        $this->params['editMode'] = Yii::app()->user->checkAccess('updateContentPage', array('page'=>$this->params['page']));
        $this->params['settings']['global'] = Yii::app()->settings->model->getAttributes();
        $len = strlen($this->params['className']);
        foreach ($this->params['settings']['global'] as $k => $v) {
            if (substr($k,0,$len+1) == $this->params['className'].'.') {
                $this->params['settings']['local'][substr($k,$len+1)] = $v;
            }
        }
    }

    public function run()
    {
        $output = '';
        $output2 = '';
        if ($this->params['editMode'])
        {
            if (method_exists($this->content, 'resizableObjects')) {
                $output2 .= $this->render('application.components.views.resizable', $this->params, true);
            }
        }

        $className = Unit::getClassNameByUnitType($this->params['unit']->type);
        $this->params['content'] = $this->content->attributes;

        $aliases = array();
        $template = $this->params['unit']->template
                        ? basename($this->params['unit']->template)
                        : Yii::app()->settings->getValue($className.'.template');

        $dirs = $this->content->getTemplateDirAliases();
        if ($template)
            foreach ($dirs as $s)
                $aliases[] = $s . '.'. $template;
        foreach ($dirs as $s)
            $aliases[] = $s . '.unit';

        foreach ($aliases as $a) {
            if (Yii::app()->controller->getViewFile($a)!==false) {
                $alias = $a;
                break;
            }
        }
        if (!isset($alias)) return false;

        foreach ($this->params as $k => $v)
        {
            if ($v instanceof CModel)
                $this->params[$k] = $v->getAttributes();
        }

        $output .= $this->render($alias, $this->params, true);
        if (trim($output) == '' && $this->params['editMode'])  {
            $output = Yii::t('cms', '[Unit "{name}" is empty on this page] - this messages showed in edit mode only', array('{name}' => call_user_func(array($className, 'unitName'))));
        }
        echo $output . $output2;
        
    }
    
    
}