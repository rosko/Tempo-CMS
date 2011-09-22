<dt><?=CHtml::label(Yii::t('InstallModule.main', $label), $name)?></dt>
<dd>
<?php if ($type == 'text') { ?>
<?=CHtml::textField($name, $value, array('id'=>$name))?>
<?php } elseif ($type == 'password') { ?>
<?=CHtml::passwordField($name, $value, array('id'=>$name))?>
<?php if ($repeat) { ?>
</dd>
<dt><?=CHtml::label(Yii::t('InstallModule.main', $label) . ' ('.Yii::t('InstallModule.main', 'repeat').')', $name.'_repeat')?></dt>
<dd>
<?=CHtml::passwordField($name.'_repeat', $value, array('id'=>$name.'_repeat'))?>
<?php } ?>
<?php } elseif ($type == 'checkbox') { ?>
<?=CHtml::checkBox($name, $value, array('id'=>$name))?>
<?php } ?>
</dd>