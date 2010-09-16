<?php $this->beginContent('//layouts/main'); ?>
<div class="container">
	<div class="span-19">
		<div id="content">
			<?php echo $content; ?>
			<?php $this->widget('Area', array('name'=>'main')); ?>
		</div><!-- content -->
	</div>
	<div class="span-5 last">
		<div id="sidebar">
			<?php $this->widget('Area', array('name'=>'right')); ?>
		</div><!-- sidebar -->
	</div>
</div>
<?php $this->endContent(); ?>