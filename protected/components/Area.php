<?php

class Area extends CWidget
{
    public $name;
    public $pageWidgets = array();
    public $readOnly = false;
    
    public function run()
    {
        if (!$this->checkAccess()) return false;

        if ($this->name == 'main') {
            $hideMainAreaOn = array('site/login');
            if (in_array(Yii::app()->controller->id.'/'.Yii::app()->controller->action->id, $hideMainAreaOn) ||
                (Yii::app()->errorHandler->error && (!Yii::app()->page->model || (Yii::app()->page->model && Yii::app()->page->model->id == 1))))
                return false;
        }

        $page = Yii::app()->page->model;

        $this->readOnly = !Yii::app()->user->checkAccess(Page::areaOperation('update', $this->name), array('object'=>$page));
        // ((substr($this->name,0,4)=='main')||!Yii::app()->settings->getValue('simpleMode'));

        if (!empty($this->pageWidgets)) {
            $pageWidgets = $this->pageWidgets;
        } else {
            if ($page) {
                $pageWidgets = $page->getWidgets($this->name);
            } else $pageWidgets = array();
        }
        $output = '';

        foreach ($pageWidgets as $i => $pageWidget) {

            $widgetClass = $pageWidget->widget->class;
            $modelClass= call_user_func(array($widgetClass,'modelClassName'));

            $cacheVaryBy = array(
                'className'=>'Widget',
                'pageWidgetId'=>$pageWidget->id,
                'id'=>$pageWidget->widget->id,
                'language'=>Yii::app()->language,
                'readOnly'=>$this->readOnly,
                'modify'=>$pageWidget->widget->modify,
            );
            $properties = array(
                'duration' => Yii::app()->settings->getValue('cacheTime'),
            );
            if (call_user_func(array($widgetClass, 'cacheable'))) {
                if (method_exists($modelClass, 'cacheDependencies')) {
                    $content = $pageWidget->widget->content;
                    $tmp = $content->cacheDependencies();
                    if (!empty($tmp) && is_array($tmp)) {
                        if (count($tmp)==1) {
                            $properties['dependency'] = $tmp[0];
                        } else {
                            $properties['dependency'] = array(
                                'class'=>'system.caching.dependencies.CChainedCacheDependency',
                                'dependencies'=>$tmp,
                            );
                        }
                    }
                }

                if (method_exists($widgetClass, 'cacheRequestTypes')) {
                    $tmp = call_user_func(array($widgetClass, 'cacheRequestTypes'));
                    if (!empty($tmp) && is_array($tmp)) {
                        $properties['requestTypes'] = $tmp;
                    }
                }

                if (method_exists($widgetClass, 'urlParams')) {
                    $tmp = call_user_func(array($widgetClass, 'urlParams'));
                    if (!empty($tmp) && is_array($tmp)) {
                        $properties['varyByParam'] = $tmp;
                    }
                }

                if (method_exists($widgetClass, 'cacheVaryBy')) {
                    $tmp = call_user_func(array($widgetClass, 'cacheVaryBy'));
                    if (!empty($tmp) && is_array($tmp)) {
                        $cacheVaryBy = CMap::mergeArray($cacheVaryBy, $tmp);
                    }
                }
            }

            $output .= $this->render('pageWidget', array(
                'pageWidget'=>$pageWidget,
                'widgetClass'=>$widgetClass,
                'readOnly'=>$this->readOnly,
                'cacheVaryBy'=>$cacheVaryBy,
                'properties'=>$properties,
            ), true);
        }
        
        $this->render('area', array(
            'name'=>$this->name,
            'output'=>$output,
            'readOnly'=>$this->readOnly,
        ));
    }

    protected function checkAccess()
    {
        return Yii::app()->user->checkAccess(Page::areaOperation('read', $this->name), array('object' => Yii::app()->page->model));
    }
}
