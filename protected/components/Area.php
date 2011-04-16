<?php

class Area extends CWidget
{
    public $name;
    
    public function run()
    {
        $editArea = Yii::app()->user->checkAccess('updatePage', array('page'=>$this->controller->loadModel())) && ((substr($this->name,0,4)=='main')||!Yii::app()->settings->getValue('simpleMode'));
        if (get_class($this->controller) == 'PageController' && $this->controller->loadModel()) {
            $pageunits = $this->controller->loadModel()->getUnits($this->name);
        } else $pageunits = array();

        $output = '';

        foreach ($pageunits as $i => $pageunit) {

            $className = Unit::getClassNameByUnitType($pageunit->unit->type);

            $cacheVaryBy = array(
                'className'=>'Unit',
                'id'=>$pageunit->unit->id,
                'language'=>Yii::app()->language,
                'editMode'=>$editArea,
                'modify'=>$pageunit->unit->modify,
            );
            $properties = array(
                'duration' => Yii::app()->settings->getValue('cacheTime'),
            );
            if (constant($className.'::CACHE')) {
                if (method_exists($className, 'cacheDependencies')) {
                    $content = $pageunit->unit->content;
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

                if (method_exists($className, 'cacheRequestTypes')) {
                    $tmp = call_user_func(array($className, 'cacheRequestTypes'));
                    if (!empty($tmp) && is_array($tmp)) {
                        $properties['requestTypes'] = $tmp;
                    }
                }

                if (method_exists($className, 'urlParams')) {
                    $tmp = call_user_func(array($className, 'urlParams'));
                    if (!empty($tmp) && is_array($tmp)) {
                        $properties['varyByParam'] = $tmp;
                    }
                }
                if (method_exists($className, 'cacheVaryBy')) {
                    $tmp = call_user_func(array($className, 'cacheVaryBy'));
                    if (!empty($tmp) && is_array($tmp)) {
                        $cacheVaryBy = CMap::mergeArray($cacheVaryBy, $tmp);
                    }
                }
            }

            $output .= $this->render('pageunit', array(
                'pageunit'=>$pageunit,
                'className'=>$className,
                'editArea'=>$editArea,
                'cacheVaryBy'=>$cacheVaryBy,
                'properties'=>$properties,
            ), true);
        }
        
        $this->render('area', array(
            'name'=>$this->name,
            'editArea'=>$editArea,
            'output'=>$output,
        ));
    }
}
