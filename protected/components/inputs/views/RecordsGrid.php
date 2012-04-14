<div id="<?=$id?>_header">
<?php if (!$recordExample->isMaxLimitReached()) { ?>
    <input type="button" class="<?=$id?>_add" value="<?=$addButtonTitle ? $addButtonTitle : Yii::t('cms', 'Add')?>" />
<?php } ?>
</div>
<?php 
if ($this->hasModel()) {
    CHtml::activeHiddenField($this->model, $this->attribute);
}
?>

<?=$recordsGrid?>

<div id="<?=$id?>_footer">
    
</div>

<script type="text/javascript">
$('#<?=$id?>_check input').die('click').live('click', function() {
    var check = $(this).attr('checked');
    var settings = $.fn.yiiGridView.settings['<?=$id?>'];
    $('#<?=$id?> .'+settings.tableClass+' > tbody > tr').each(function(i){
        if (check) {
            $(this).addClass('cms-selected');
        } else {
            $(this).removeClass('cms-selected');
        }
    });
});
$('.<?=$id?>_add').click(function() {

    <?php if ($pageId) { ?>

        var url = '/?r=widget/edit&area=<?=$area?>&pageId=<?=$pageId?>&modelClass=<?=$className?>&sectionId=<?=$model->id?>&foreignAttribute=<?=$foreignAttribute?>&language='+$.data(document.body, 'language');
        cmsLoadDialog(url, {
            simpleClose: false,
            onOpen: function(t) {
                $(t).find('.field_title input:eq(0)').focus();
            },
            onClose: function() {
                $.fn.yiiGridView.update('<?=$id?>');
            }
        });

    <?php } else { 
        // Иначе просто создаем запись
        ?>        
        if ($.fn.yiiGridView) {
            cmsRecordEditForm(0, '<?=$className?>', 0, '<?=$id?>');
        } else {
            cmsRecordEditForm(0, '<?=$className?>', 0);
        }

    <?php } ?>


});
</script>
