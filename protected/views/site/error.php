<?php
$this->pageTitle=Yii::t('cms', 'Error');
?>

<h2><?=Yii::t('cms', 'Error')?> <?php echo $code; ?></h2>

<div class="error">
<?php echo CHtml::encode($message); ?>
</div>