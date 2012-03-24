// =============================================================

function cmsShowSelectWidgetDialog(t)
{
    var pageWidget = $(t).parents('.cms-pagewidget');
    if (pageWidget.length) {
        pageWidget = pageWidget.eq(0);
        var pageWidgetId = pageWidget.attr('id').replace('cms-pagewidget-','');
    } else {
        var pageWidgetId = '0';
    }
    var areaName = $(t).parents('.cms-area').eq(0).attr('id').replace('cms-area-','');
    selectWidgetDialog = $('#cms-pagewidget-add').clone().attr('id', 'cms-pagewidget-addsplash');
    selectWidgetDialog.find('.cms-btn-pagewidget-create').attr('rel', areaName);
    selectWidgetDialog.find('.cms-btn-pagewidget-create').attr('rev', pageWidgetId);
    cmsOpenDialog(selectWidgetDialog);
}

// =============================================================
