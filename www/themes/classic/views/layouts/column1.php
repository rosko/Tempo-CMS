<?php $this->beginContent('//layouts/main'); ?>
<div class="container">
	<div id="content">
		<?php echo $content; ?>
		<?php $this->widget('Area', array('name'=>'main')); ?>
	</div><!-- content -->
</div>
<?php $this->endContent(); ?>