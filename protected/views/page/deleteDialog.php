<img style="float:left;margin-right:1em;" valign="baseline" src="/images/icons/fatcow/32x32/page_delete.png" />
<h3><?=Yii::t('cms', 'This page consists children pages')?></h3>
<?=CHtml::form("/?r=page/delete&pageId={$model->id}");?>
<input type="submit" id="deletechildren" name="deletechildren" value="<?=Yii::t('cms', 'Delete with all children pages')?>" /><br />
<input type="submit" id="movechildren" name="movechildren" value="<?=Yii::t('cms', 'Delete this page, and move children pages on other place')?>" />
<?=Yii::t('cms', 'To page')?>:
<br />
<?php
$this->widget('PageSelect', array(
        'model'=>$model,
        'attribute'=>'parent_id',
        'name'=>'newParent',
        'size'=>60
    ));
?>
    
<script type="text/javascript">
$(function() {
    $('input[type=submit]').button().width('90%');
});
</script>

</form>
