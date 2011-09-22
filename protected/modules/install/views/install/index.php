<?php
Yii::app()->getClientScript()->registerCssFile($assets.'/style.css');
$status = true;
?>

<?=CHtml::beginForm()?>
<?=CHtml::hiddenField('step', $step)?>
<?php foreach ($_POST as $k => $v) { if (in_array($k,array('next','previous','step',Yii::app()->getRequest()->csrfTokenName))) continue; ?>
    <?=CHtml::hiddenField($k, $v)?>
<?php } ?>
<h1><?=Yii::t('InstallModule.main', 'Step {step}/{steps}', array('{step}'=>$step+1,'{steps}'=>count($config['wizard'])))?></h1>
<dl class="commands">
<?php foreach ($result as $command) {
    
    if (!$command['status'] || $command['alwaysShow'] || $config['mode']=='verbose') {
        $command['type'] = $command[1];
        echo $this->renderPartial('command'.ucfirst($command[0]), $command);
    }
    $status = $status && (!empty($command['status']) || !empty($command['canSkip']));
}  ?>

</dl>
<?php
?>
<?php if ($step > 0) { ?>
<?=CHtml::submitButton(Yii::t('InstallModule.main','Previous'), array('name'=>'previous'))?>
<?php } ?>
<?=CHtml::submitButton(Yii::t('InstallModule.main','Refresh'), array('name'=>'refresh'))?>
<?php if (!empty($config['wizard'][$step+1])) {
    echo CHtml::submitButton(Yii::t('InstallModule.main','Next'), array('name'=>'next', 'disabled'=>!$status));
} ?>

<?=CHtml::endForm()?>