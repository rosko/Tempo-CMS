<?php

class Area extends CWidget
{
    public $name;
    public $pageUnits = array();
    public $readOnly = false;
    
    public function run()
    {
        $page = Yii::app()->page->model;
        $editArea = !$this->readOnly && !Yii::app()->user->isGuest && ((substr($this->name,0,4)=='main')||!Yii::app()->settings->getValue('simpleMode'));
        if (!empty($this->pageUnits)) {
            $pageUnits = $this->pageUnits;
        } else {
            if ($page) {
                $pageUnits = $page->getUnits($this->name);
            } else $pageUnits = array();
        }
        $output = '';

        foreach ($pageUnits as $i => $pageUnit) {

            $widgetClass = $pageUnit->unit->class;
            $modelClass= call_user_func(array($widgetClass,'modelClassName'));

            $cacheVaryBy = array(
                'className'=>'Unit',
                'pageUnitId'=>$pageUnit->id,
                'id'=>$pageUnit->unit->id,
                'language'=>Yii::app()->language,
                'editMode'=>$editArea,
                'modify'=>$pageUnit->unit->modify,
            );
            $properties = array(
                'duration' => Yii::app()->settings->getValue('cacheTime'),
            );
            if (call_user_func(array($widgetClass, 'cacheable'))) {
                if (method_exists($modelClass, 'cacheDependencies')) {
                    $content = $pageUnit->unit->content;
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

            $output .= $this->render('pageUnit', array(
                'pageUnit'=>$pageUnit,
                'widgetClass'=>$widgetClass,
                'editArea'=>$editArea,
                'cacheVaryBy'=>$cacheVaryBy,
                'properties'=>$properties,
            ), true);
        }
        
        $this->render('area', array(
            'name'=>$this->name,
            'editArea'=>$editArea,
            'output'=>$output,
            'readOnly'=>$this->readOnly,
        ));
    }
}
