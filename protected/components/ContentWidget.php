<?php

class ContentWidget extends CWidget
{
    public $params;
    public $content;
    public $template='main';
    
    public function name($language=null)
    {
        return false;
    }
    
    public function icon()
    {
        return false;
    }

    public function version()
    {
        return false;
    }

    public function templates()
    {
        return array(
            'main' => Yii::t('cms', 'Main template'),
        );
    }

    public function init()
    {
        $this->params['className'] = get_class($this->content);
        $this->params['widget'] = $this->content->widget;
        $this->params['content'] = $this->content;
        $this->params['isGuest'] = Yii::app()->user->isGuest;
        $this->params['language'] = Yii::app()->language;
        $get = $_GET;
        unset($get['alias'],$get['pageId'],$get['url'],$get['language']);
        $this->params['getParams'] = http_build_query($get);
        $this->params['user'] = Yii::app()->user->data;
        $this->params['templateType'] = 'main';
        $this->params['page'] = Yii::app()->page->model;
        $this->params['editMode'] = !Yii::app()->user->isGuest;
        $this->params['settings']['global'] = Yii::app()->settings->model->getAttributes();
        $len = strlen($this->params['className']);
        foreach ($this->params['settings']['global'] as $k => $v) {
            if (substr($k,0,$len+1) == $this->params['className'].'.') {
                $this->params['settings']['local'][substr($k,$len+1)] = $v;
            }
        }
    }

    public function urlParam($method)
    {
        return strtolower(substr(get_class($this),4)).$this->id.'_'.$method;
    }
    
    public static function urlParams()
    {
        return array();
    }

    public function cacheable()
    {
        return true;
    }

    public function cacheRequestTypes()
    {
        return array('GET', 'POST');
    }

    public function cacheVaryBy()
    {
        return array();
    }

    public function getPageVar()
    {
        return $this->urlParam('page');
    }

    public function getPageNumber()
    {
        return intval(@$_GET[$this->getPageVar()]);
    }    
    
    public function renderPager($showedCount, $itemCount, $currentPage, $pageSize=0, $pageId=0, $pagerCssClass='')
    {
        if ($showedCount < $itemCount) {
            foreach ($_GET as $k => $v) {
                if (substr($k,-5)=='_page' && !isset($_REQUEST[$k])) {
                    unset($_GET[$k]);
                }
            }
            $pagination = new CPagination($itemCount);
            if ($pageSize < 1)
                $pageSize = Yii::app()->settings->getValue('defaultsPerPage');
            $pagination->pageVar = $this->getPageVar();
            $pagination->pageSize = $pageSize;
            $pagination->currentPage = $currentPage-1;
            $pagination->route = 'view/index';
            $params = $_GET;
            if (Yii::app()->controller->id == 'view' && Yii::app()->controller->action->id == 'widget') {
                unset($params['pageWidgetId']);
                unset($params['_']);
            }
            $pagination->params = array_merge($params, array(
                'pageId' => Yii::app()->page->model->id,
                'alias' => Yii::app()->page->model->alias,
                'url' => Yii::app()->page->model->url,
            ));
            $pagerCssClass .= Yii::app()->settings->getValue('ajaxPager') ? ' ajaxPager ' : '';
            return Yii::app()->controller->widget('CLinkPager', array(
                'pages'=>$pagination,
                'htmlOptions'=>array(
                    'class'=> 'yiiPager ' . $pagerCssClass,
                ),
                'maxButtonCount'=>5), true);
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

        $className = call_user_func(array($this->params['widget']->class,'unitClassName'));
        $this->params['content'] = $this->content->attributes;

        $aliases = array();
        $template = $this->params['widget']->template[$this->params['templateType']]
                        ? basename($this->params['widget']->template[$this->params['templateType']])
                        : Yii::app()->settings->getValue($className.'.template');
        if ($this->params['widget']->template[$this->params['templateType']]) {
            $template = basename($this->params['widget']->template[$this->params['templateType']]);
        } else {
            $tpl = Yii::app()->settings->getValue($className.'.template');
            $template = $tpl[$this->params['templateType']];
        }

        $dirs = $this->content->getTemplateDirAliases($className);
        if ($template)
            foreach ($dirs as $s)
                $aliases[] = $s . '.'. $this->params['templateType'].'-'.$template;
        foreach ($dirs as $s)
            $aliases[] = $s . '.'.$this->params['templateType'].'-default';

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
            $output = Yii::t('cms', '[Widget "{name}" is empty on this page] - this messages showed in edit mode only', array('{name}' => call_user_func(array($this->params['widget']['class'], 'name'))));
        }
        echo $output . $output2;
        
    }
    
    public static function getInstalledWidgets()
    {
        $units = ContentUnit::getInstalledUnits(true);
        $return = array();
        
        $unitConfig = ContentUnit::loadConfig();
        
        foreach ($units as $unitClassName => $name) {
            $unit = array(
                'className' => $unitClassName,
                'name' => $name,
                'icon' => call_user_func(array($unitClassName, 'icon')),
                'widgets' => array(),
            );
            $dir = strtolower(substr($unitClassName,4));
            $widgets = call_user_func(array($unitClassName, 'widgets'));
            foreach ($widgets as $className => $alias) {
                if (is_int($className)) {
                    $className = $alias;
                    $alias = $unitConfig[$unitClassName]. '.' . $dir . '.' . $className;
                }
                $unit['widgets'][] = array(
                    'className' => $className,
                    'name' => call_user_func(array($className, 'name')),
                    'icon' => call_user_func(array($className, 'icon')),
                );
            }            
            $return[] = $unit;            
        }
        return $return;
    }

}