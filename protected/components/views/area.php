<?php
    $editArea = Yii::app()->user->checkAccess('updatePage', array('page'=>$this->controller->loadModel())) && ((substr($name,0,4)=='main')||!Yii::app()->settings->getValue('simpleMode'));
?>
<div id="cms-area-<?=$name?>" class="<?php if ($editArea) { ?>cms-area <?php } ?>area">

<?php
if (get_class($this->controller) == 'PageController' && $this->controller->loadModel()) {
$pageunits = $this->controller->loadModel()->getUnits($name);
foreach ($pageunits as $pageunit) {

    $className = Unit::getClassNameByUnitType($pageunit->unit->type);

    $cacheVaryBy = array(
        'className'=>'Unit',
        'id'=>$pageunit->unit->id,
        'language'=>Yii::app()->language,
        'editMode'=>$editArea,
        'modify'=>$pageunit->unit->modify,
    );
    $properties = array(
        'duration' => 3600,
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

    if(!constant($className.'::CACHE') || $this->beginCache(serialize($cacheVaryBy), $properties)) {
        
        $content = $pageunit->unit->content;
        if ($content) {
    ?>

    <div <?php if ($editArea) { ?>title="<?php echo call_user_func(array($className, 'name')); ?>"<?php } ?>
        id="cms-pageunit-<?=$pageunit->id?>" class="<?php if ($editArea) { ?>cms-pageunit <? } ?>pageunit cms-unit-<?=$pageunit->unit->type?>"
        rel="<?=$pageunit->unit->type?>" rev="<?=$pageunit->unit->id?>" content_id="<?=$content->id?>">
        <?=$content->run(array('pageunit'=>$pageunit));?>
    </div>

<?php
        }
        if (constant($className.'::CACHE'))
            $this->endCache();
    }
}
?>

<?php
}
?>
</div>

