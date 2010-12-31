<?php
    $editArea = Yii::app()->user->checkAccess('updatePage') && ((substr($name,0,4)=='main')||!Yii::app()->settings->getValue('simpleMode'));
?>
<div id="cms-area-<?=$name?>" class="<?php if ($editArea) { ?>cms-area <?php } ?>area">

<?php
if (get_class($this->controller) == 'PageController' && $this->controller->_model) {
$pageunits = $this->controller->_model->getUnits($name);
foreach ($pageunits as $pageunit) {

    $id = $pageunit->unit->id . '_' . $pageunit->unit->modify;
    foreach (Page::defaultAccess() as $o => $r) {
        $id .= '_'.Yii::app()->user->checkAccess($o.'Page');
    }
    if($this->beginCache($id, array('duration'=>3600))) {

        $className = Unit::getClassNameByUnitType($pageunit->unit->type);
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

