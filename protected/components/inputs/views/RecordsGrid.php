<?php
    $title = call_user_func(array($class_name, 'name')) . ' ' . date('Y-m-d H:i');
    $dataAdd = CJavaScript::quote('Page[title]=' . $title . '&Page[parent_id]=' . $page_id . '&Page[keywords]=&Page[description]=&go=1');
?>
<div id="<?=$id?>_header">
    <input type="button" class="<?=$id?>_add" value="<?=Yii::t('cms', 'Add')?>" />
</div>

<?=$records_grid?>

<div id="<?=$id?>_footer">
    
</div>

<script type="text/javascript">
$('#<?=$id?>_check input').live('click', function() {
    var check = $(this).attr('checked');
    var settings = $.fn.yiiGridView.settings['<?=$id?>'];
    $('#<?=$id?> .'+settings.tableClass+' > tbody > tr').each(function(i){
        if (check) {
            $(this).addClass('selected');
        } else {
            $(this).removeClass('selected');
        }
    });
});
$('.<?=$id?>_add').click(function() {
    var new_page_id = 0;
    if (<?=$page_id?> > 0) {
        $.ajax({
            url:'/?r=page/pageAdd&json=1',
            cache: false,
            success: function(html) {
                if (html.substring(0,2) == '{"') {
                    var ret = jQuery.parseJSON(html);
                    if (ret) {
                        ajaxSave('/?r=page/pageAdd&id=<?=$page_id?>&_='+ret.underscore, '<?=$dataAdd?>&'+ret.unique_id+'=1', 'POST', function(html){
                            if (html.substring(0,2) == '{"') {
                                var ret = jQuery.parseJSON(html);
                                if (ret) {
                                    new_page_id = ret.id;
                                    ajaxSave('/?r=page/unitAdd', 'pageunit_id=0&area=<?=$area?>&page_id='+new_page_id+'&type=<?=$type?>&section_id=<?=$section_id?>&foreign_attribute=<?=$foreign_attribute?>&content_page_id='+new_page_id, 'GET', function(html) {
                                        $.fn.yiiGridView.update('<?=$id?>');
                                        if (html.substring(0,2) == '{"') {
                                            var ret = jQuery.parseJSON(html);
                                            if (ret) {
                                                recordEditForm(ret.content_id, '<?=$class_name?>', ret.unit_id, '<?=$id?>');
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    }
                }
            }
        });
    } else {
        // Иначе просто создаем запись
        ajaxSave('/?r=records/create&class_name=<?=$class_name?>&section_id=<?=$section_id?>&foreign_attribute=<?=$foreign_attribute?>', '', 'GET', function(html){
            $.fn.yiiGridView.update('<?=$id?>');
            if (html.substring(0,2) == '{"') {
                var ret = jQuery.parseJSON(html);
                if (ret) {
                    recordEditForm(ret.id, '<?=$class_name?>', '', '<?=$id?>');
                }
            }
        });
        
    }


});
</script>
