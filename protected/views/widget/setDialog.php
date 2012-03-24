<?php
    $page_select_name = 'set_page_ids';

    $cs=Yii::app()->getClientScript();
    $cs->registerPackage('jstree');
    $cs->registerScript('widgetSetDialog', <<<JS

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


    $('#cms-pagewidget-set-select').toggle(function() {
        $('#cms-dlg-select-set-page').show();
    }, function () {
        $('#cms-dlg-select-set-page').hide();
    });

    $('.cms-btn-pagemap-openall').click(function() {
        $('#pagetree_{$page_select_name}').jstree('open_all');
        return false;
    });
    $('.cms-btn-pagemap-closeall').click(function() {
        $('#pagetree_{$page_select_name}').jstree('close_all');
        return false;
    });

    $('#cms-pagewidget-set-select-ok').submit(function() {
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
        var url = '/?r=widget/set&widgetId={$widgetId}&pageWidgetId={$pageWidgetId}';
        var data = decodeURIComponent($.param({'pageIds': ids}));
        cmsAjaxSave(url, data, 'POST');
        if (cur_page) {
            $('#cms-pagewidget-{$pageWidgetId}').remove();
        }
        cmsCloseDialog();
        cmsAreaEmptyCheck();
        return false;
    });
    $('#cms-pagewidget-set-select-ok').parents('div:eq(0)').width(440);

});

JS
);

?>
<div class="cms-caption">
<img style="float:left;margin-right:1em;" valign="baseline" src="/images/icons/fatcow/32x32/application_cascade.png" />
<h3><?=Yii::t('cms', 'Widget location on pages')?>:</h3>
</div>
<?=Yii::t('cms', 'Check')?>:
<br />
<select id="cms-sel-pagemap-select" class="cms-btn-pagemap-select">
    <option value="all"><?=Yii::t('cms', 'All pages')?></option>
    <option value="current"><?=Yii::t('cms', 'Current page only')?></option>
    <option value="current+children"><?=Yii::t('cms', 'Current and children pages only')?></option>
    <option value="all-current"><?=Yii::t('cms', 'All pages, except current')?></option>
    <option value="all-current-children"><?=Yii::t('cms', 'All, except current and children pages')?></option>
    <option value="none"><?=Yii::t('cms', 'Nothing')?></option>
</select>
<input type="button" value="Ок" class="cms-btn-pagemap-select" />
<br /><br />

<a href="#" class="cms-btn-pagemap-openall"><?=Yii::t('cms', 'Expand all')?></a> / <a href="#" class="cms-btn-pagemap-closeall"><?=Yii::t('cms', 'Collapse all')?></a>
<br /><br />
<?php
    $pages = $widget->pages;
    $pagesIds = array();
    if ($pages && is_array($pages))
    {
        foreach ($pages as $p) {
            $pagesIds[] = $p->id;
        }
    }

    $this->widget('PageSelect', array(
        'model'=>$model,
        'attribute'=>'id',
        'width'=>420,
        'name'=>$page_select_name,
        'excludeCurrent'=>false,
        'multiple'=>true,
        'checkedOnly'=>$pagesIds,
    ));
?>
<br />
<form id="cms-pagewidget-set-select-ok">
<input type="submit" value="<?=Yii::t('cms', 'Set widget on selected pages only')?>" />
</form>
