<?php
    $editArea = Yii::app()->user->checkAccess('updatePage') && ((substr($name,0,4)=='main')||!Yii::app()->settings->getValue('simpleMode'));
?>
<div id="cms-area-<?=$name?>" class="<?php if ($editArea) { ?>cms-area <?php } ?>area">

<?php
if (get_class($this->controller) == 'PageController' && $this->controller->_model) {
$pageunits = $this->controller->_model->getUnits($name);
foreach ($pageunits as $pageunit) {

    $className = Unit::getClassNameByUnitType($pageunit->unit->type);
    
    $cacheParams = array(
        'id'=>$pageunit->unit->id,
        'modify'=>$pageunit->unit->modify,
        'language'=>Yii::app()->language,
    );
    if (method_exists($className, 'cacheParams')) {
        $p = call_user_func(array($className, 'cacheParams'));
        if (is_array($p))
            $cacheParams = array_merge($cacheParams, $p);        
    }
    if (method_exists($className, 'defaultAccess')) {
        foreach(call_user_func(array($className, 'defaultAccess')) as $o => $r) {
            $cacheParams[$o.$className] = Yii::app()->user->checkAccess($o.$className);
        }
    }
    foreach (Page::defaultAccess() as $o => $r) {
        $cacheParams[$o.'Page'] = Yii::app()->user->checkAccess($o.'Page');
    }
    foreach (Unit::defaultAccess() as $o => $r) {
        $cacheParams[$o.'Unit'] = Yii::app()->user->checkAccess($o.'Unit');
    }
    if($this->beginCache(serialize($cacheParams), array('duration'=>3600))) {

        $content = $pageunit->unit->content;

    ?>

    <div <?php if (Yii::app()->user->checkAccess('updatePage')) { ?>title="<?php echo call_user_func(array($className, 'name')); ?>"<?php } ?> 
        id="cms-pageunit-<?=$pageunit->id?>" class="<?php if ($editArea) { ?>cms-pageunit <? } ?>pageunit cms-unit-<?=$pageunit->unit->type?>"
        rel="<?=$pageunit->unit->type?>" rev="<?=$pageunit->unit->id?>" content_id="<?=$content->id?>">
        <?=$content->run(array('pageunit'=>$pageunit));?>
    </div>

<?php 
        $this->endCache();     
    }
}
?>

<?php
}
?>
</div>

