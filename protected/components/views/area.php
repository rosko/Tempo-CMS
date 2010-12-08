<?php
    $editArea = !Yii::app()->user->isGuest && ((substr($name,0,4)=='main')||!Yii::app()->settings->getValue('simpleMode'));
?>
<div id="cms-area-<?=$name?>" class="<?php if ($editArea) { ?>cms-area <?php } ?>area">

<?php
if (get_class($this->controller) == 'PageController' && $this->controller->_model) {
$units = $this->controller->_model->getUnits($name);
foreach ($units as $unit) {
    
    $className = Unit::getClassNameByUnitType($unit->unit->type);

    ?>

    <div <?php if (!Yii::app()->user->isGuest) { ?>title="<?php echo call_user_func(array($className, 'name')); ?>"<?php } ?> id="cms-pageunit-<?=$unit->id?>" class="<?php if ($editArea) { ?>cms-pageunit <? } ?>pageunit cms-unit-<?=$unit->unit->type?>" rel="<?=$unit->unit->type?>" rev="<?=$unit->unit->id?>">

        <?=$unit->unit->content->run(array('pageunit'=>$unit));?>

    </div>

<?php } ?>

<?php
}
?>
</div>

