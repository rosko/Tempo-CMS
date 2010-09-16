<?php

    $cs=Yii::app()->getClientScript();
    $cs->registerScript('pageunitDeleteConfirm', <<<EOD

$(function() {
    $('#cms-pageunit-remove-this').attr('rel', '/?r=page/pageunitRemove&pageunit_id[]={$pageunit_id}&unit_id={$unit_id}').attr('rev', {$pageunit_id});
    $('#cms-pageunit-remove-all').attr('rel', '/?r=page/pageunitRemove&pageunit_id=all&unit_id={$unit_id}').attr('rev', {$unit_id});

    // Обработчик нажатия кнопки "Удалить этот юнит только тут"
    $('#cms-pageunit-remove-this').click(function() {
        if (confirm('Вы действительно хотите удалить этот блок?'))
        {
            var pageunit_id = $(this).attr('rev');
            ajaxSave($(this).attr('rel'), '', 'GET', function(ret) {
                hideSplash();
                GetOutPageunitPanel();
                $('#cms-pageunit-'+pageunit_id).remove();
            });
        }
        return false;
    });

    // Обработчик нажатия кнопки "Удалить этот юнит везде"
    $('#cms-pageunit-remove-all').click(function() {
        if (confirm('Вы действительно хотите удалить этот блок во всех местах? Удаляемая информация будет безвозвратно потеряна.'))
        {
            var unit_id = $(this).attr('rev');
            ajaxSave($(this).attr('rel'), '', 'GET', function(ret) {
                hideSplash();
                GetOutPageunitPanel();
                $('.cms-pageunit[rev='+unit_id+']').remove();
            });
        }
        return false;
    });
    $('#cms-pageunit-remove-select').toggle(function() {
        $('#cms-dlg-select-page').show();
        $.fancybox.resize();
    }, function () {
        $('#cms-dlg-select-page').hide();
        $.fancybox.resize();
    });
    
    $('.cms-btn-pagemap-openall').click(function() {
        $('#pagetree_page_ids').jstree('open_all');
        return false;
    });
    $('.cms-btn-pagemap-closeall').click(function() {
        $('#pagetree_page_ids').jstree('close_all');
        return false;
    });
    
    $('#cms-pageunit-remove-select-ok').click(function() {
        if (confirm('Вы действительно хотите удалить этот блок со всех отмеченных страниц? Удаляемая информация будет безвозвратно потеряна.'))
        {
            var checked = $('#pagetree_page_ids').jstree('get_checked');
            var ids = new Array();
            var cur_page = false;
            var id = 0;
            checked.each(function(){
                id = $(this).children('a:eq(0)').attr('rel');
                ids.push(id);
                if (id == {$model->id}) {
                    cur_page = true;
                }
                
            });
            var url = '/?r=page/pageunitRemove&unit_id={$unit_id}'
            var data = decodeURIComponent($.param({'page_ids': ids}));
            ajaxSave(url, data, 'POST');
            if (cur_page) {
                hideSplash();
                GetOutPageunitPanel();
                $('#cms-pageunit-{$pageunit_id}').remove();
                CmsAreaEmptyCheck();
            }
        }
        return false;
    });
    $('#cms-dlg-select-page').parents('div:eq(0)').width(440);
    
});

EOD
);

?>

<h3>Удалить этот блок:</h3>
<ul>
    <li><a class="cms-button w400" id="cms-pageunit-remove-this" href="#">Только из этой страницы</a></li>
    <li><a class="cms-button w400" id="cms-pageunit-remove-all" href="#">Со всех страниц</a></li>
    <li><a class="cms-button w400" id="cms-pageunit-remove-select" href="#">Выборочно</a>
<div id="cms-dlg-select-page" class="hidden">
<br />
<a href="#" class="cms-btn-pagemap-openall">Раскрыть все</a> / <a href="#" class="cms-btn-pagemap-closeall">Скрыть все</a>
<br /><br />
<?php
    $pages = $unit->pages;
    $pages_ids = array();
    if ($pages && is_array($pages))
    {
        foreach ($pages as $page) {
            $pages_ids[] = $page->id;
        }
    }
    
    $this->widget('PageSelect', array(
        'model'=>$model,
        'attribute'=>'id',
        'width'=>400,
        'name'=>'page_ids',
        'excludeCurrent'=>false,
        'multiple'=>true,
        'checked'=>array($model->id),
        'enabledOnly'=>$pages_ids,
    ));
?>
<br />
<a class="cms-button w400" id="cms-pageunit-remove-select-ok" href="#">Удалить блок с выбранных страниц</a>
</div>
    </li>
</ul>
