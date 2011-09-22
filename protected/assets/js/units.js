// =============================================================

function cmsShowSelectUnitTypeDialog(t)
{
    var pageUnit = $(t).parents('.cms-pageunit');
    if (pageUnit.length) {
        pageUnit = pageUnit.eq(0);
        var pageUnitId = pageUnit.attr('id').replace('cms-pageunit-','');
    } else {
        var pageUnitId = '0';
    }
    var areaName = $(t).parents('.cms-area').eq(0).attr('id').replace('cms-area-','');
    selectUnitTypeDialog = $('#cms-pageunit-add').clone().attr('id', 'cms-pageunit-addsplash');
    selectUnitTypeDialog.find('.cms-btn-pageunit-create').attr('rel', areaName);
    selectUnitTypeDialog.find('.cms-btn-pageunit-create').attr('rev', pageUnitId);
    cmsOpenDialog(selectUnitTypeDialog);
}

// =============================================================
