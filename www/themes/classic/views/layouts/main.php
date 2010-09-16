<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title><?php echo CHtml::encode($this->pageTitle);
	$sitename = Yii::app()->settings->getValue('sitename');
	if ($sitename) {
		echo ' - ' . $sitename;
	}
	?></title>

<?php if ($this->description) { ?>
	<meta name="description" content="<?=$this->description?>" />
<?php } ?>
<?php if ($this->keywords) { ?>
	<meta name="keywords" content="<?=$this->keywords?>" />
<?php } ?>

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />

</head>

<body>

<div class="container" id="page">

	<div id="header">
		<div id="logo"><a href="/"><?php echo CHtml::encode($sitename ? $sitename : 'blank'); ?></a></div>
	</div><!-- header -->

	<div id="mainmenu">
		<?php $this->widget('Area', array('name'=>'top')); ?>
	</div><!-- mainmenu -->

	<?php echo $content; ?>

	<div id="footer">
		Copyright &copy; <?php echo date('Y'); ?><br/>
		All Rights Reserved<br/>
<?php if (Yii::app()->user->isGuest) { ?>
		<a href="<?=$this->createUrl('site/login')?>">Управление сайтом</a><br />
<?php } ?>
	</div><!-- footer -->

</div><!-- page -->

</body>
</html>