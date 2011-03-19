<?php
/*
        $_fields = array(
            array(
                'type'=>'text',
                'name'=>'pole1',
                'label'=>array(
                    'ru'=>'Поле1',
                    'en'=>'Field1',
                ),
                'hint'=>array(
                    'ru'=>'Подсказка для поля1',
                    'en'=>'Hint for field 1',
                ),
                'rules'=>array(
                    array('required'),
                    array('length', 'min'=>3, 'max'=>15),
                ),
            ),
            array(
                'type'=>'checkboxlist',
                'name'=>'pole2',
                'label'=>array(
                    'ru'=>'Поле2',
                    'en'=>'Field2',
                ),
                'hint'=>array(
                    'ru'=>'Подсказка для поля2',
                    'en'=>'Hint for field2',
                ),
                'rules'=>array(
                ),
                'items'=>array(
                    '1'=>array(
                        'ru'=>'Опция1',
                        'en'=>'Option1',
                    ),
                    '2'=>array(
                        'ru'=>'Опция2',
                        'en'=>'Option2',
                    ),
                    '3'=>array(
                        'ru'=>'Опция3',
                        'en'=>'Option3',
                    ),
                ),
            ),
        );

$fields = $_fields;*/
?>
<div id="<?=$id?>_fields" class="FieldSet_fields">
<?php if (is_array($fields)) foreach ($fields as $k=>$field) {
    if (!in_array($field['type'], $this->allowTypes)) continue;
    ?>
    <?php Yii::app()->controller->renderPartial('application.components.inputs.views.FieldSetField', array(
        'id'=>$id,
        'name'=>$name,
        'k'=>$k,
        'field'=>$field,
        'langs'=>$langs,
        'FS'=>$this,
    )); ?>
<?php } ?>
</div>
<div>
    <?php
        $l = $this->typesLabels();
        $fields = array();
        foreach ($this->allowTypes as $type) {
            $fields[$type] = $l[$type];
        }
    ?>
    <?=CHtml::dropDownList('none', null, $fields, array(
        'id'=>$id.'_selectfield'
    ));?>
    <input type="button" value="<?=Yii::t('cms', 'Add field')?>" id="<?=$id?>_addfield" />
</div>
<!-- <a href="#" id="<?=$id?>_data">data</a> //-->

<div class="hidden">
    <div id="<?=$id?>_sample">
    <?php foreach ($this->allowTypes as $type) { ?>
        <div id="<?=$id?>_sample_field_<?=$type?>">
            <?php Yii::app()->controller->renderPartial('application.components.inputs.views.FieldSetField', array(
                'id'=>$id,
                'name'=>$name,
                'k'=>-1,
                'field'=>array('type'=>$type),
                'langs'=>$langs,
                'FS'=>$this,
            )); ?>
        </div>
    <?php } ?>
    </div>

    <?php foreach($this->allowTypes as $type) { ?>
    <div id="<?=$id?>_type_<?=$type?>">
        
        
    </div>
    <?php } ?>

</div>

<script type="text/javascript">
$('#<?=$id?>_fields').parents('form:eq(0)').submit(function() {
    $('#<?=$id?>_sample').remove();
    $('#<?=$id?>_fields').find('.FieldSet_field_rules').find('fieldset').each(function() {
        if ($(this).hasClass('hidden') || $(this).css('display')=='none') {
            $(this).remove();
        }
    });
    return true;
});
$('#<?=$id?>_fields').sortable({
    cursor: 'hand'
});
$('#<?=$id?>_fields').find('.FieldSet_field_toggleoptions').die('click').live('click', function(){
    options = $(this).parents('.FieldSet_field:eq(0)').find('.FieldSet_field_options');
    if (options.css('display')=='none') {
        options.slideDown('normal', function() {
            checkHeight<?=$id?>(this);
        });
        $(this).text('<?=Yii::t('cms', 'Hide options')?>');
    } else {
        options.slideUp('normal', function() {
            checkHeight<?=$id?>(this);
        });
        $(this).text('<?=Yii::t('cms', 'Show options')?>');        
    }    
    return false;
});
$('#<?=$id?>_fields').find('.FieldSet_field_togglehint').die('click').live('click', function(){
    $(this).parents('.FieldSet_field:eq(0)').find('.FieldSet_field_hint').slideToggle('normal', function(){
        checkHeight<?=$id?>(this);
    });
    return false;
});
$('#<?=$id?>_fields').find('.FieldSet_field_togglerules').die('click').live('click', function(){
    $(this).parents('.FieldSet_field:eq(0)').find('.FieldSet_field_rules').slideToggle('normal', function(){
        checkHeight<?=$id?>(this);
    });
    return false;
});
$('#<?=$id?>_fields').find('.FieldSet_field_toggleattrs').die('click').live('click', function(){
    $(this).parents('.FieldSet_field:eq(0)').find('.FieldSet_field_attrs').slideToggle('normal', function(){
        checkHeight<?=$id?>(this);
    });
    return false;
});


$('#<?=$id?>_fields').find('.FieldSet_field_delete').die('click').live('click', function(){
    $(this).parents('.FieldSet_field:eq(0)').fadeOut('normal', function(){
        $(this).remove();
        checkHeight<?=$id?>(this);
    });
    return false;
});
$('#<?=$id?>_addfield').click(function(){
    var ftype = $('#<?=$id?>_selectfield').val();
    var field = $('#<?=$id?>_sample_field_'+ftype).children().clone(false);
    field.find('input[rel=type]').val(ftype);

    i = 0;
    do {
        i++;
        n = ftype+i.toString();
    }
    while ($('#<?=$id?>_fields').find('input[rel=name]').filter(function() {
        return $(this).val() == n;
    }).length > 0);
    field.find('input[rel=name]').val(n);
    
    var num = getMaxNum<?=$id?>()+1;
    field.attr('rel', num);
    var s = '<?=$name?>[-1]';
    s = s.replace(/\[/g, '\\[').replace(/\]/g, '\\]');
    field.html(field.html().replace(new RegExp(s, 'gm'), '<?=$name?>['+num+']'));
    field.appendTo('#<?=$id?>_fields');
    checkHeight<?=$id?>(this);
    return false;
});

$('#<?=$id?>_data').click(function(){
    var r = '';
    jQuery.map($(this).parents('form:eq(0)').serializeArray(), function(n, i){
        r += n['name'] +'='+ n['value']+"\n";
    });
    alert(r);
    return false;
});

function getMaxNum<?=$id?>() {
    var max = 0;
    $('#<?=$id?>_fields').find('.FieldSet_field').each(function() {
        max = Math.max(max, $(this).attr('rel'));
    });
    return max;
}

function checkHeight<?=$id?>(t) {
    tab = $(t).parents('.ui-tabs-panel:eq(0)');
    if (!tab.length) {
        tab = $(t).parents('form:eq(0)');
    }
    if ($(tab).height() > $(window).height()*0.65) {
        $(tab).height(Math.ceil($(window).height()*0.65)).css({'overflow-y':'auto'});
    } else {
        $(tab).height('auto');
    }
}
</script>