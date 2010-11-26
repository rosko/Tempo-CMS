<?php
    $page_select_name = 'set_page_ids';

    $cs=Yii::app()->getClientScript();
    $cs->registerScript('unitSetDialog', <<<EOD

$(function() {
    $('.cms-btn-pagemap-select').click(function() {
        var v = $('#cms-sel-pagemap-select').val();
        if (v == 'all') {
            $('#pagetree_{$page_select_name}').jstree('check_node_all', '#pagetree_{$page_select_name}-1');

        } else if (v == 'current') {
            $('#pagetree_{$page_select_name}').jstree('uncheck_node_all', '#pagetree_{$page_select_name}-1');
            $('#pagetree_{$page_select_name}').jstree('check_node', '#pagetree_{$page_select_name}-{$model->id}');
            $('#{$page_select_name}_dialog').scrollTo($('#pagetree_{$page_select_name}-{$model->id}'), 500);

        } else if (v == 'current+children') {
            $('#pagetree_{$page_select_name}').jstree('uncheck_node_all', '#pagetree_{$page_select_name}-1');
            $('#pagetree_{$page_select_name}').jstree('check_node_all', '#pagetree_{$page_select_name}-{$model->id}');
            $('#{$page_select_name}_dialog').scrollTo($('#pagetree_{$page_select_name}-{$model->id}'), 500);

        } else if (v == 'all-current') {
            $('#pagetree_{$page_select_name}').jstree('check_node_all', '#pagetree_{$page_select_name}-1');
            $('#pagetree_{$page_select_name}').jstree('uncheck_node', '#pagetree_{$page_select_name}-{$model->id}');
            $('#{$page_select_name}_dialog').scrollTo($('#pagetree_{$page_select_name}-{$model->id}'), 500);

        } else if (v == 'all-current-children') {
            $('#pagetree_{$page_select_name}').jstree('check_node_all', '#pagetree_{$page_select_name}-1');
            $('#pagetree_{$page_select_name}').jstree('uncheck_node_all', '#pagetree_{$page_select_name}-{$model->id}');
            $('#{$page_select_name}_dialog').scrollTo($('#pagetree_{$page_select_name}-{$model->id}'), 500);

        } else if (v == 'none') {
            $('#pagetree_{$page_select_name}').jstree('uncheck_node_all', '#pagetree_{$page_select_name}-1');
            
        }
        return false;
    });


    $('#cms-pageunit-set-select').toggle(function() {
        $('#cms-dlg-select-set-page').show();
        resizeSplash();
    }, function () {
        $('#cms-dlg-select-set-page').hide();
        resizeSplash();
    });

    $('.cms-btn-pagemap-openall').click(function() {
        $('#pagetree_{$page_select_name}').jstree('open_all');
        return false;
    });
    $('.cms-btn-pagemap-closeall').click(function() {
        $('#pagetree_{$page_select_name}').jstree('close_all');
        return false;
    });

    $('#cms-pageunit-set-select-ok').click(function() {
        var checked = $('#pagetree_{$page_select_name}').jstree('get_checked');
        var ids = new Array();
        var cur_page = true;
        var id = 0;
        checked.each(function(){
            id = $(this).children('a:eq(0)').attr('rel');
            ids.push(id);
            if (id == {$model->id}) {
                cur_page = false;
            }

        });
        if (ids.length == 0) {
            cur_page = false;
        }
        var url = '/?r=page/unitSet&unit_id={$unit_id}&pageunit_id={$pageunit_id}';
        var data = decodeURIComponent($.param({'page_ids': ids}));
        ajaxSave(url, data, 'POST');
        if (cur_page) {
            $('#cms-pageunit-{$pageunit_id}').remove();
        }
        hideSplash();
        GetOutPageunitPanel();
        CmsAreaEmptyCheck();
        return false;
    });
    $('#cms-pageunit-set-select-ok').parents('div:eq(0)').width(440);

});

EOD
);

?>

<h3>Размещение блока на страницах:</h3>
Отметить:
<br />
<select id="cms-sel-pagemap-select" class="cms-btn-pagemap-select">
    <option value="all">Все страницы</option>
    <option value="current">Только текущую страницу</option>
    <option value="current+children">Только текущую и все дочерние</option>
    <option value="all-current">Все, кроме текущей</option>
    <option value="all-current-children">Все, кроме текущей и ее дочерних страниц</option>
    <option value="none">Ничего</option>
</select>
<input type="button" value="Ок" class="cms-btn-pagemap-select" />
<br /><br />

<a href="#" class="cms-btn-pagemap-openall">Раскрыть все</a> / <a href="#" class="cms-btn-pagemap-closeall">Скрыть все</a>
<br /><br />
<?php
    $pages = $unit->pages;
    $pages_ids = array();
    if ($pages && is_array($pages))
    {
        foreach ($pages as $p) {
            $pages_ids[] = $p->id;
        }
    }

    $this->widget('PageSelect', array(
        'model'=>$model,
        'attribute'=>'id',
        'width'=>420,
        'name'=>$page_select_name,
        'excludeCurrent'=>false,
        'multiple'=>true,
        'checkedOnly'=>$pages_ids,
    ));
?>
<br />
<a class="cms-button w400" id="cms-pageunit-set-select-ok" href="#">Разместить блок только на отмеченных страницах</a>
