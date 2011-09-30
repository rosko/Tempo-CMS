<div id="<?=$id?>_header">
    <input type="button" class="<?=$id?>_add" value="<?=$addButtonTitle ? $addButtonTitle : Yii::t('cms', 'Add')?>" />
</div>

<?=$recordsGrid?>

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
    var newPageId = 0;
    
    if (<?=$pageId?>) {

        var url = '/?r=unit/edit&area=<?=$area?>&makePage=<?=(int)$this->makePage?>&pageId=<?=$pageId?>&type=<?=$type?>&sectionId=<?=$sectionId?>&foreignAttribute=<?=$foreignAttribute?>&language='+$.data(document.body, 'language');
        cmsLoadDialog(url, {
            simpleClose: false,
            onClose: function() {
                $.fn.yiiGridView.update('<?=$id?>');
                cmsReloadPageUnit(<?=$pageUnitId?>, '.cms-pageunit[rev=<?=$unitId?>]');
            }
        });

    } else { 
        // Иначе просто создаем запись
        if ($.fn.yiiGridView) {
            cmsRecordEditForm(0, '<?=$className?>', 0, '<?=$id?>');
        } else {
            cmsRecordEditForm(0, '<?=$className?>', 0);
            $('#cmsRecordEditForm<?=$className?>_0').live('dialogbeforeclose', function() {
                cmsReloadPageUnit(<?=$pageUnitId?>, '.cms-pageunit[rev=<?=$unitId?>]');
            });
        }

    }


});
</script>
