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

<?php
$confirmText = Yii::t('cms', 'Are you really want delete?');
$operations = array(
    'delete' => array(
        'title' => Yii::t('cms', 'Delete'),
        'click' => 'js:function(gridId, elem)'.<<<JS
{
    if (confirm('{$confirmText}')) {
        var ids = $.fn.yiiGridView.getSelection(gridId);
        cmsAjaxSave('/?r=records/delete&className={$className}&'+$.param({id: ids}), '', 'GET', function(){
            $.fn.yiiGridView.update(gridId);
        });
    }
}
JS
    ),
);

if (method_exists($recordExample, 'listOperations')) {
    $operations = array_merge($operations, call_user_func(array($recordExample, 'listOperations')));
}

foreach ($operations as $opName => $opParams) {
    if (!isset($opParams['type'])) {
        $operations[$opName]['type'] = 'option';
    }
}

?>


<div id="<?=$id?>_footer">

    <a href="#select-all" id="<?=$id?>_selectall"><?php echo Yii::t('cms', 'Select all'); ?></a> /
    <a href="#unselect-all" id="<?=$id?>_unselectall"><?php echo Yii::t('cms', 'Unselect all'); ?></a><br />
    <label for="<?=$id?>_operationtype"><?php echo Yii::t('cms', 'With selected'); ?>:</label>
    <select id="<?=$id?>_operationtype">
        <option selected="selected"></option>
        <?php foreach ($operations as $opName => $opParams) { ?>
        <<?=$opParams['type']?> value="<?=$opName?>"><?=$opParams['title']?></<?=$opParams['type']?>>
        <?php } ?>
    </select>
    <input type="button" id="<?=$id?>_operationdo" value="<?php echo Yii::t('cms', 'Ok'); ?>" />

    <div id="<?=$id?>_footeradv"></div>
    
</div>

<script type="text/javascript">

<?php foreach ($operations as $opName => $opParams) { ?>
    var <?=$opName?>_operation_<?=$id?> = <?=CJavaScript::encode($opParams['click']);?>;
<?php } ?>


$('#<?=$id?>_selectall').click(function() {
    var settings = $.fn.yiiGridView.settings['<?=$id?>'];
    $('#<?=$id?> .'+settings.tableClass+' > tbody > tr').each(function(i){
        $(this).addClass('selected');
        $(this).find('input[type=checkbox]').attr('checked', true);
    });
    $('#<?=$id?>_check input').attr('checked', true);
    return false;
});

$('#<?=$id?>_unselectall').click(function() {
    var settings = $.fn.yiiGridView.settings['<?=$id?>'];
    $('#<?=$id?> .'+settings.tableClass+' > tbody > tr').each(function(i){
        $(this).removeClass('selected');
        $(this).find('input[type=checkbox]').attr('checked', false);
    });
    $('#<?=$id?>_check input').attr('checked', false);
    return false;
});

$('#<?=$id?>_operationtype').bind('change', function() {
    $('#<?=$id?>_operationdo').click();
});

$('#<?=$id?>_operationdo').click(function() {
    $('#<?=$id?>_footeradv').html('');
    var funcName = $('#<?=$id?>_operationtype').val() + '_operation_<?=$id?>';
    if ($.isFunction(window[funcName])) {
        window[funcName]('<?=$id?>', this);
    }
    return false;
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
