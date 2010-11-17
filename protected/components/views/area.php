<?php
    $this->controller->areas[] = $name;

    $editArea = !Yii::app()->user->isGuest && ((substr($name,0,4)=='main')||!Yii::app()->settings->getValue('simpleMode'));
?>
<div id="cms-area-<?=$name?>" class="<?php if ($editArea) { ?>cms-area <?php } ?>area">

<?php
if (get_class($this->controller) == 'PageController' && $this->controller->_model) {
$units = $this->controller->_model->getUnits($name);
foreach ($units as $unit) {
    
    $className = Unit::getClassNameByUnitType($unit->unit->type);

    ?>

    <div <?php if (!Yii::app()->user->isGuest) { ?>title="<?php echo $className::NAME; ?>"<?php } ?> id="cms-pageunit-<?=$unit->id?>" class="<?php if ($editArea) { ?>cms-pageunit <? } ?>pageunit cms-unit-<?=$unit->unit->type?>" rel="<?=$unit->unit->type?>" rev="<?=$unit->unit->id?>">

        <?php
            $output = $this->render('application.units.views.'.$className,
                                           array('unit'=>$unit->unit,
                                                 'pageunit'=>$unit,
                                                 'content'=>$unit->unit->content,
                                                 'page'=>$this->controller->_model), true);
            if (trim($output) == '' && !Yii::app()->user->isGuest)  {
                $output = '[Блок "'.$className::NAME.'" на этой странице пуст] - это сообщение отображается только в режиме редактирования';
            }
            echo $output;
        ?>

    </div>

<?php } ?>

<?php
}
?>
</div>

