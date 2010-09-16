<?php
    $this->controller->areas[] = $name;
?>
<div id="cms-area-<?=$name?>" class="cms-area">

<?php
if (get_class($this->controller) == 'PageController' && $this->controller->_model) {
$units = $this->controller->_model->getUnits($name);
foreach ($units as $unit) {
    
    $className = 'Unit'.ucfirst(strtolower($unit->unit->type));

    ?>

    <div <?php if (!Yii::app()->user->isGuest) { ?>title="<?php echo $className::NAME; ?>"<?php } ?> id="cms-pageunit-<?=$unit->id?>" class="cms-pageunit cms-unit-<?=$unit->unit->type?>" rel="<?=$unit->unit->type?>" rev="<?=$unit->unit->id?>">

        <?php
            $this->render('application.units.views.Unit'.ucfirst(strtolower($unit->unit->type)),
                                           array('unit'=>$unit->unit,
                                                 'content'=>$unit->unit->content,
                                                 'page'=>$this->controller->_model));
        ?>

    </div>

<?php } ?>

<?php
}
?>
</div>

