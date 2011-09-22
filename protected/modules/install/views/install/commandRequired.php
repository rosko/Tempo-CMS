<dt><?=Yii::t('InstallModule.commandRequired', $title, $t)?></dt>
<dd>
<img src="<?=$assets?>/<?=($status?'tick.png':'cross.png')?>" />
<?php if ($message) { ?>
<?=Yii::t('InstallModule.commandRequired', $message, $t)?>
<?php } ?>
</dd>