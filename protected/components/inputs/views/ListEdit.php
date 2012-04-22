<div id="<?=$id?>_fields" class="ListEdit_fields">
    <?php if (is_array($items)) foreach ($items as $i=>$item) { ?>
        <?php Yii::app()->controller->renderPartial('application.components.inputs.views.ListEditField', array(
            'id'=>$id,
            'name'=>$name,
            'i'=>$i,
            'item'=>$item,
            'langs'=>$langs,
            'LE'=>$this,
        )); ?>
    <?php } ?>
</div>

<div>
    <input type="button" value="<?=Yii::t('cms', 'Add item')?>" id="<?=$id?>_additem" />
</div>

<div class="cms-hidden">
    <div id="<?=$id?>_sample">
        <?php Yii::app()->controller->renderPartial('application.components.inputs.views.ListEditField', array(
            'id'=>$id,
            'name'=>$name,
            'i'=>'-1',
            'langs'=>$langs,
            'LE'=>$this,
        )); ?>
    </div>
</div>

<script type="text/javascript">
$('#<?=$id?>_fields').parents('form:eq(0)').submit(function() {
    $('#<?=$id?>_sample').remove();
});
$('#<?=$id?>_fields').sortable({
    cursor: 'hand'
});
$('#<?=$id?>_fields').find('.ListEdit_field_toggleoptions').die('click').live('click', function(){
    options = $(this).parents('.ListEdit_field:eq(0)').find('.ListEdit_field_options');
    if (options.css('display')=='none') {
        options.slideDown('normal', function() {
            cmsDialogResize(this);
        });
        $(this).text('<?=Yii::t('cms', 'Hide translates')?>');
    } else {
        options.slideUp('normal', function() {
            cmsDialogResize(this);
        });
        $(this).text('<?=Yii::t('cms', 'Show translates')?>');
    }
    return false;
});

$('#<?=$id?>_fields').find('.ListEdit_field_delete').die('click').live('click', function(){
    $(this).parents('.ListEdit_field:eq(0)').fadeOut('normal', function(){
        $(this).remove();
        cmsDialogResize(this);
    });
    return false;
});
$('#<?=$id?>_additem').click(function(){
    var field = $('#<?=$id?>_sample').children().clone(false);

    var num = getMaxNum<?=$id?>()+1;
    field.attr('rel', num);
    var s = '<?=$name?>[-1]';
    s = s.replace(/\[/g, '\\[').replace(/\]/g, '\\]');
    field.html(field.html().replace(new RegExp(s, 'gm'), '<?=$name?>['+num+']'));

    field.appendTo('#<?=$id?>_fields');
    cmsDialogResize(this);
    return false;
});

function getMaxNum<?=$id?>() {
    var max = 0;
    $('#<?=$id?>_fields').find('.ListEdit_field').each(function() {
        max = Math.max(max, $(this).attr('rel'));
    });
    return max;
}

</script>