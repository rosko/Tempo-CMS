// =============================================================

function cmsShowSelectWidgetDialog(t)
{
    var pageUnit = $(t).parents('.cms-pageunit');
    if (pageUnit.length) {
        pageUnit = pageUnit.eq(0);
        var pageUnitId = pageUnit.attr('id').replace('cms-pageunit-','');
    } else {
        var pageUnitId = '0';
    }
    var areaName = $(t).parents('.cms-area').eq(0).attr('id').replace('cms-area-','');
    selectWidgetDialog = $('#cms-pageunit-add').clone().attr('id', 'cms-pageunit-addsplash');
    selectWidgetDialog.find('.cms-btn-pageunit-create').attr('rel', areaName);
    selectWidgetDialog.find('.cms-btn-pageunit-create').attr('rev', pageUnitId);
    cmsOpenDialog(selectWidgetDialog);
}

// =============================================================
