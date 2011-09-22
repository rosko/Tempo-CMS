<?php if (!empty($file)) { echo $this->renderPartial($file); } else { ?>
<dt><h2><?=Yii::t('InstallModule.main', $title, $t)?></h2></dt>
<dd>
<?php if ($message) { ?>
<?=Yii::t('InstallModule.main', $message, $t)?>
<?php } ?>
</dd>
<?php } ?>
