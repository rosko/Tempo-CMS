// =============================================================

// Картинка изображающая процесс загрузки
var cms_html_loading_image = '<img src="/images/ajax-loader.gif" />';
var cms_infopanel_time_timer = false;
var cms_save_timer = false;
var cms_saveprocess_timer = null;
// Лимит количества попыток сохранения
    var cms_save_tries_limit = 10;
// Минимальный интервал между повторными попытками сохранения
var cms_save_repeattime = 5;
var cms_save_max_repeattime = 1800;
var cms_current_command = null;

var cms_save_commands = new Array();

function CmsCommand(url, data, method, success, silent)
{
    this.url = url;
    this.data = data;
    this.method = method;
    this.success = success;
    this.silent = silent;
}

function ajaxSaveArea(area, name, pageId, add_params)
{
    var url = '/?r=unit/move&pageId='+pageId+'&area='+name+'&'+add_params;
    var params = $(area).sortable('serialize', {
        'key':'pageUnits[]'
    });
    ajaxSave(url, params);
}

function ajaxSave(url, data, method, success, silent)
{
    cms_save_commands.push(new CmsCommand(url, data, method, success, silent));
    ajaxSaveProcess();
}

function ajaxSaveProcess()
{
    if (!cms_current_command && !cms_save_timer) {
        cms_current_command = cms_save_commands.shift();
        if (!cms_current_command.tries) cms_current_command.tries = 0;
        cms_current_command.tries++;
        if (!cms_current_command.method) {
            cms_current_command.method = 'POST';
        }
        if (cms_current_command.method.toUpperCase()== 'POST') {
            cms_current_command.data += '&'+$.data(document.body, 'csrfTokenName')+'='+$.data(document.body, 'csrfToken');
        }
        $.ajax({
            url: cms_current_command.url,
            data: cms_current_command.data,
            cache: false,
            type: cms_current_command.method,
            beforeSend: function() {
                if (!cms_current_command.silent)
                    cmsShowInfoPanel(cms_html_loading_image, 0);
            },
            success: function(ret) {
                if (!cms_current_command.silent)
                    cmsHideInfoPanel();
                if (ret != 0) {
                    if ($.isFunction(cms_current_command.success)) {
                        cms_current_command.success(ret);
                    }
                    ajaxSaveDone();
                    cms_current_command = null;
                    if (cms_save_commands.length > 0) {
                        ajaxSaveProcess();
                    }
                } else {
                    ajaxSaveRepeat(cms_current_command.tries*cms_save_repeattime);
                }
            },
            error: function() {
                ajaxSaveRepeat(cms_current_command.tries*cms_save_repeattime);
            }
        });
    }
}

function ajaxSaveDone()
{
    if ((!cms_current_command) ||
        (cms_current_command && !cms_current_command.silent) )
        cmsShowInfoPanel(i18n.cms.saved);

}

function ajaxSaveRepeat(timeout)
{
    if (timeout == null) {
        timeout = cms_save_repeattime;
    }
    if (timeout > cms_save_max_repeattime) {
        timeout = cms_save_max_repeattime;
    }
    cms_save_commands.unshift(cms_current_command);
    cms_current_command = null;

    cmsHideInfoPanel();
    cmsShowInfoPanel(i18n.cms.savingError + ' <span id="cms-info-timer"></span>. <a href="#" id="cms-info-button-repeat">'+i18n.cms.retryNow+'</a>', timeout-1, true);
    $('#cms-info-button-repeat').unbind('click').bind('click',function() {
        cmsHideInfoPanel();
        cms_save_timer = false;
        clearTimeout(cms_saveprocess_timer);
        ajaxSaveProcess();
    });
    clearTimeout(cms_infopanel_time_timer);
    cmsShowInfoPanelTimer(timeout);
    cms_save_timer = true;
    cms_saveprocess_timer = setTimeout("cms_save_timer=false;ajaxSaveProcess()", timeout*1000);
}

function cmsShowInfoPanelTimer(timeout)
{
    var t = new Date(timeout*1000);
    var minutes = t.getMinutes().toString();
    if (minutes.length == 1) {
        minutes = '0' + minutes;
    }
    var seconds = t.getSeconds().toString();
    if (seconds.length == 1) {
        seconds = '0' + seconds;
    }
    $('#cms-info-timer').html(minutes+':'+seconds);
    if (timeout > 1) {
        cms_infopanel_time_timer = setTimeout("cmsShowInfoPanelTimer("+(timeout-1)+")", 1000);
    }
}

function getRealBgColor(elem)
{
    if (elem.css('backgroundColor') != 'transparent') {
        return elem.css('backgroundColor');
    } else {
        return getRealBgColor(elem.parent());
    }
}

function fadeIn(selector, className)
{
    $(selector).removeClass('hover').addClass(className);
}

function fadeOut(selector, className)
{
    var elems = $(selector);
    elems.removeClass('hover');
    elems.each(function(){
        color1 = $(this).css('backgroundColor');
        $(this).removeClass(className);
        color2 = $(this).css('backgroundColor');
        color3 = color2;
        if (color2 == 'transparent') {
            color3 = getRealBgColor($(this));
        }
        $(this).css('backgroundColor', color1).animate({backgroundColor: color3}, {
            duration: 1000,
            complete: function() {
                $(this).css('backgroundColor', color2);
            }
        });
    });
}

// =============================================================

// Вспомогательные мелкие функции

function clearSelection()
{
    if (window.getSelection) {        // Firefox, Opera, Safari
        var selection = window.getSelection ();
        selection.removeAllRanges ();
    }
    else {
        if (document.selection.createRange) {        // Internet Explorer
            var range = document.selection.createRange ();
            document.selection.empty ();
        }
    }
}

function trim(string)
{
    return string.replace(/(^\s+)|(\s+$)/g, "");
}

function getAreaNameByPageUnit(pageUnit)
{
    return $(pageUnit).parents('.cms-area').eq(0).attr('id').replace('cms-area-', '');
}

function getAreaByPageUnit(pageUnit)
{
    return $(pageUnit).parents('.cms-area').eq(0);
}

function getAreaName(area)
{
    return $(area).attr('id').replace('cms-area-', '');
}

function sanitizeAlias(str)
{
    str = str.replace(/[\s\:\.]/gi, '-')
    while (str.indexOf('--')>-1) {
        str = str.replace(/\-\-/gi, '-');
    }
    return str.replace(/[^0-9A-Za-zА-Яа-я-]*/gi, '');
}

function makeUrl(alias, oldurl)
{
    var p = oldurl.split('/');
    p[p.length-1] = alias;
    return p.join('/');
}


function ajaxSubmitForm(form, data, hasError, events)
{
    var btn_name = form.attr('rev');
    if (!hasError) {
        // Сохранить юнит
        if (!btn_name) { btn_name = 'save'; }
        var params = form.serialize() + '&' + btn_name +'=1';
        ajaxSave(form.attr('action'), params, form.attr('method'), function(html) {
            // Обновить на странице
            if (events != undefined && $.isFunction(events.onSuccess)) {
                events.onSuccess(html);
            }

            var pageUnitId = form.attr('rel');
            if (pageUnitId != undefined) {
                var pageUnit = $('#cms-pageunit-'+pageUnitId);
                var unitId = pageUnit.attr('rev');
                //alert(pageunit.attr('rev'));
                cmsReloadPageUnit(pageUnitId, '.cms-pageunit[rev='+unitId+']');
            }
            // Или обновить таблицу записей
            if (form.data('grid_id') !== undefined) {
                $.fn.yiiGridView.update(form.data('grid_id'));
            }
            if (html.substring(0,2) == '{"') {
                var ret = jQuery.parseJSON(html);
                if (ret) {
                    location.href = ret.url;
                }
            } else {
                $(form).attr('action', $(html).find('form').attr('action'));
            }
            if (btn_name == 'refresh') {
                location.href = '/?r=view/index&pageId='+$('body').attr('rel')+'&language='+$.data(document.body, 'language');
            } else if (btn_name != 'apply') {
                if ($(form).parents('.cms-dialog').length) {
                    var dlg = $(form).parents('.cms-dialog');
                    $(dlg).dialog('close');
                }
                $(form).remove();
                cmsCloseDialog();
            }

        });

    } else {
        $('#'+form.attr('id')+' .errorMessage').hide().html('');
        $('#'+form.attr('id')+' .row').removeClass('error');
        $.each(data, function(i,item){
            $('#'+i.replace('.','\\.')+'_em_').show().html(item.toString());
            $('#'+i.replace('.','\\.')).parent('.row:eq(0)').addClass('error');
            $('#'+i.replace('.','\\.')).parentsUntil('#'+form.attr('id')).each(function() {
                $(this).show();
                if ($(this).hasClass('ui-accordion-content') && !$(this).hasClass('ui-accordion-content-active')) {
                    $(this).parent().accordion('activate', $(this));
                }
                if ($(this).hasClass('ui-tabs-panel') && $(this).hasClass('ui-tabs-hide')) {
                    $(this).parent().tabs('select', '#'+$(this).attr('id'));
                }
            });
            $('#'+i.replace('.','\\.')).focus();
            return false;
        });
    }
    return false;
}

// =============================================================


function cmsAreaEmptyCheck()
{
    $('.cms-area').each(function() {
        if ($(this).find('.cms-pageunit').length > 0) {
            $(this).find('.cms-empty-area-buttons').remove();
        } else {
            var btn = $('<a class="cms-button w100p" title="'+i18n.cms.addAnotherUnit+'" href="#">'+i18n.cms.addUnit+'</a>')
                .click(function() {
                    pageUnitAddForm(this);
                    return false;
                });
            $(this).html('').append($('<div class="cms-empty-area-buttons"></div>').append(btn));
        }
    });
}

function pageUnitAddForm(t)
{
    var pageUnit = $(t).parents('.cms-pageunit');
    if (pageUnit.length) {
        pageUnit  = pageUnit.eq(0);
        var pageUnitId = pageUnit.attr('id').replace('cms-pageunit-','');
    } else {
        var pageUnitId = '0';
    }
    var area_name = $(t).parents('.cms-area').eq(0).attr('id').replace('cms-area-','');
    pageUnitAdd = $('#cms-pageunit-add').clone().attr('id', 'cms-pageunit-addsplash');
    pageUnitAdd.find('.cms-btn-pageunit-create').attr('rel', area_name);
    pageUnitAdd.find('.cms-btn-pageunit-create').attr('rev', pageUnitId);
    cmsOpenDialog(pageUnitAdd);
}

// =============================================================


function pageUnitEditForm(t)
{
    var pageUnit = $(t);
    if (pageUnit.hasClass('selected')) {return;}
    fadeIn(pageUnit, 'selected');
    pageUnitId = pageUnit.attr('id').replace('cms-pageunit-','');
    unitType = pageUnit.attr('rel');
    cmsLoadDialog('/?r=unit/edit&type='+unitType+'&pageUnitId='+pageUnitId+'&language='+$.data(document.body, 'language'), {
        pageUnit: pageUnit,
        pageUnitId: pageUnitId,
        simpleClose: false,
        onClose: function() {
            cmsReloadPageUnit(pageUnitId, '.cms-pageunit[rev='+pageUnit.attr('rev')+']');
        }
    });
}

function pageUnitDeleteDialog(unitId, pageUnitId, pageId)
{
    $.ajax({
        url: '/?r=unit/getPageUnitsByUnitId&pageId='+pageId+'&unitId='+unitId+'&language='+$.data(document.body, 'language'),
        pageUnitId: pageUnitId,
        unitId: unitId,
        pageId: pageId,
        type: 'GET',
        cache: false,
        beforeSend: function() {
            cmsShowInfoPanel(cms_html_loading_image, 0);
        },
        success: function(html) {
            cmsHideInfoPanel();
            var ids = jQuery.parseJSON(html);
            if (ids.length > 1)
            {
                cmsLoadDialog('/?r=unit/deleteDialog&pageId='+pageId+'&unitId='+unitId+'&pageUnitId='+pageUnitId+'&language='+$.data(document.body, 'language'));
            }
            else {
                if (confirm(i18n.cms.deleteUnitWarning))
                {
                    ajaxSave('/?r=unit/delete&pageId='+pageId+'&pageUnitId[]='+pageUnitId+'&unitId='+unitId+'&language='+$.data(document.body, 'language'), '', 'GET', function(ret) {
                        $('#cms-pageunit-'+pageUnitId).remove();
                        cmsAreaEmptyCheck();
                    });
                } else {
                    fadeOut('.selected', 'selected');
                }
            }
        }
    });

}

function pageUnitSetDialog(pageId, pageUnitId, unitId)
{
    cmsLoadDialog('/?r=unit/setDialog&pageId='+pageId+'&unitId='+unitId+'&pageUnitId='+pageUnitId+'&language='+$.data(document.body, 'language'), {
        onLoad: function() {
            $.topbox.clear();
        }
    });
}

// =============================================================


function pageAddForm()
{
    cmsLoadDialog('/?r=page/add&pageId='+$('body').attr('rel')+'&language='+$.data(document.body, 'language'), {
        simpleClose: false,
        onOpen: function() {
            $('#Page_title').focus();
            $('#Page_parent_id').val($('body').attr('rel'));
            $('#Page_parent_id_title').val($.data(document.body, 'title'));
        }
    });
}


function pageEditForm()
{
    var pageId = $('body').attr('rel');
    cmsLoadDialog('/?r=page/edit&pageId='+pageId+'&language='+$.data(document.body, 'language'), {
        simpleClose: false,
        onOpen: function(html) {
            $('#Page_title').focus();
            var formId = $(html).find('form').attr('id');
            var v = $('#'+formId).find('input[name=deletepage]').val();
            $(html).find('button').each(function() {
                if ($(this).text() == v) {
                    $(this).unbind('click').bind('click', function() {
                        pageDeleteDialog(null, function() {
                            ajaxSave($('#'+formId).attr('action'), $('#'+formId).serialize()+'&delete=1', 'POST', function(html) {
                                if (html.substring(0,2) == '{"') {
                                    var ret = jQuery.parseJSON(html);
                                    if (ret) {
                                      location.href = ret.url;
                                    }
                                }
                            });
                        }, function(html) {
                            if (html.substring(0,2) == '{"') {
                                var ret = jQuery.parseJSON(html);
                                if (ret) {
                                  location.href = ret.url;
                                }
                            } else
                            if ($(html).find('form').eq(0).find('.errorMessage').length == 0) {
                                location.reload();
                            }
                        });
                        return false;
                    });
                }
            });
        }
    });
}

function pageDeleteDialog(pageId, onOneDelete, onChildrenDelete, onCancel)
{
    if (!pageId) {
        pageId = $('body').attr('rel');
    }
    $.ajax({
        url:'/?r=page/hasChildren&pageId='+pageId+'&language='+$.data(document.body, 'language'),
        cache: false,
        pageId: pageId,
        onChildrenDelete: onChildrenDelete,
        onCancel: onCancel,
        beforeSend: function() {
            cmsShowInfoPanel(cms_html_loading_image, 0);
        },
        success: function(html) {
            cmsHideInfoPanel();
            if (html == '0') {
                if (confirm(i18n.cms.deletePageWarning))
                {
                    // В функции должно быть ручное удаление страницы
                    if ($.isFunction(onOneDelete)) {
                        onOneDelete(this);
                    }
                } else {
                    if ($.isFunction(onCancel)) {
                        onCancel(this);
                    }
                }
            } else {
                cmsLoadDialog('/?r=page/deleteDialog&pageId='+pageId+'&language='+$.data(document.body, 'language'), {
                    ajaxify: true,
                    onClose: function() {
                        if ($.isFunction(onCancel)) {
                            onCancel(this);
                        }
                    },
                    onSave: function(html) {
                        if ($.isFunction(onChildrenDelete)) {
                            onChildrenDelete(html);
                        }
                    }
                });
            }
        }
    });
}

// =============================================================


function recordEditForm(id, className, unitId, gridId)
{
    var dlgId = 'recordEditForm'+className+'_'+id;
    cmsLoadDialog('/?r=unit/edit&className='+className+'&recordId='+id+'&language='+$.data(document.body, 'language'), {
        simpleClose: false,
        id: dlgId,
        className: 'recordEditForm-'+className,
        title: i18n.cms.editing,
        onOpen: function() {
            $('#'+dlgId).find('form').data('grid_id', gridId);
            $('#'+dlgId).find('label[for="Unit_title"]:eq(0)').next().focus();
        }
    });
}

function recordDelete(id, className, unitId, gridId)
{
    if (unitId > 0) {
        // Удаляем юнит
        $.ajax({
            url:'/?r=unit/check&unitId='+unitId+'&className='+className+'&pageId='+id+'&language='+$.data(document.body, 'language'),
            cache: false,
            id: id,
            className: className,
            unitId: unitId,
            beforeSend: function() {
                cmsShowInfoPanel(cms_html_loading_image, 0);
            },
            success: function(html) {
                cmsHideInfoPanel();
                if (html.substring(0,2) == '{"') {
                    var ret = jQuery.parseJSON(html);
                    if (ret.page) {
                        if (ret.page.similarToParent) {
                            recordDeleteConfirm(unitId, gridId, '&withPage=1');
                        } else {
                            var buttons = {};
                            buttons[i18n.cms.deleteUnitOnly] = function() {
                                recordDeleteConfirm(unitId, gridId);
                                $(this).dialog('close');
                            };
                            buttons[i18n.cms.deleteUnitAndPage] = function() {
                                recordDeleteConfirm(unitId, gridId, '&withPage=1');
                                $(this).dialog('close');
                            };
                            var dlgId = 'UnitDeleteConform_'+unitId;
                            var dlg = $('#cms-dialog').clone().attr('id', dlgId).addClass('cms-dialog').appendTo('body');
                            dlg.html('<span class="ui-icon ui-icon-alert" style="float:left; margin:0 10px 100px 0;"></span>'+t(i18n.cms.deleteUnitRecordConfirm, {'{page}': '(<a target="_blank" href="'+ret.page.url+'">'+ret.page.title+'</a>)'}));
                            dlg.dialog({
                                title: i18n.cms.deletingUnit,
                                modal: true,
                                zIndex: 10000,
                                buttons: buttons,
                                close: function() {
                                    $('#'+dlgId).remove();
                                }
                            });
                        }
                    } else {
                        recordDeleteConfirm(unitId, gridId);
                    }
                } else {
                    recordDeleteConfirm(unitId, gridId);
                }
            }
        });
    } else {
        // Удаляем просто запись
        if (confirm(i18n.cms.deleteRecordWarning))
        {
            ajaxSave('/?r=records/delete&id='+id+'&className='+className+'&language='+$.data(document.body, 'language'), '', 'GET', function(ret) {
                $.fn.yiiGridView.update(gridId);
            });
        }
    }
}

function recordDeleteConfirm(unitId, gridId, data, str)
{
    if (str == undefined) {
        var str = i18n.cms.deleteRecordWarning;
    }
    if (data == undefined) {
        var data = '';
    }
    if (confirm(str))
    {
        ajaxSave('/?r=unit/delete&unitId='+unitId+'&pageUnitId=all'+data+'&language='+$.data(document.body, 'language'), '', 'GET', function(ret) {
            if (gridId !== undefined) {
                $.fn.yiiGridView.update(gridId);
            }
        });
    }
}

function gotoRecordPage(id, className)
{
    $.ajax({
        url:'/?r=records/getUrl&className='+className+'&id='+id+'&language='+$.data(document.body, 'language'),
        cache: false,
        id: id,
        className: className,
        beforeSend: function() {
            cmsShowInfoPanel(cms_html_loading_image, 0);
        },
        success: function(ret) {
            cmsHideInfoPanel();
            if (ret) {
                location.href = ret;
            }
        }
    });
}
