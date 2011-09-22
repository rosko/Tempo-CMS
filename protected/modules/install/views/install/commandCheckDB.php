<dt><?=Yii::t('InstallModule.commandCheckDB', $title, $t)?></dt>
<dd>
<img src="<?=$assets?>/<?=($status?'tick.png':'cross.png')?>" />
<?php if ($message) { ?>
<?=Yii::t('InstallModule.commandCheckDB', $message, $t)?>
<?php } ?>
</dd>