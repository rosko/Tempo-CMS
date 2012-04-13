// =============================================================

// Картинка изображающая процесс загрузки
var cmsHtmlLoadingImage = '<img src="/images/ajax-loader.gif" />';
var cmsInfoPanelTimeTimer = false;
var cmsSaveTimer = false;
var cmsSaveProcessTimer = null;
// Минимальный интервал между повторными попытками сохранения
var cmsSaveRepeatTime = 5;
var cmsSaveMaxRepeatTime = 1800;
var cmsCurrentCommand = null;

var cmsSaveCommands = new Array();

function CmsCommand(url, data, method, success, silent)
{
    this.url = url;
    this.data = data;
    this.method = method;
    this.success = success;
    this.silent = silent;
}

function cmsAjaxSaveArea(area, name, pageId, add_params)
{
    var url = '/?r=widget/move&pageId='+pageId+'&area='+name+'&'+add_params;
    var params = $(area).sortable('serialize', {
        'key':'pageWidgets[]'
    });
    cmsAjaxSave(url, params);
}

function cmsAjaxSave(url, data, method, success, silent)
{
    cmsSaveCommands.push(new CmsCommand(url, data, method, success, silent));
    cmsAjaxSaveProcess();
}

function cmsAjaxSaveProcess()
{
    if (!cmsCurrentCommand && !cmsSaveTimer) {
        cmsCurrentCommand = cmsSaveCommands.shift();
        if (!cmsCurrentCommand.tries) cmsCurrentCommand.tries = 0;
        cmsCurrentCommand.tries++;
        if (!cmsCurrentCommand.method) {
            cmsCurrentCommand.method = 'POST';
        }
        if (cmsCurrentCommand.method.toUpperCase()== 'POST') {
            cmsCurrentCommand.data += '&'+$.data(document.body, 'csrfTokenName')+'='+$.data(document.body, 'csrfToken');
        }
        $.ajax({
            url: cmsCurrentCommand.url,
            data: cmsCurrentCommand.data,
            cache: false,
            type: cmsCurrentCommand.method,
            beforeSend: function() {
                if (!cmsCurrentCommand.silent)
                    cmsShowInfoPanel(cmsHtmlLoadingImage, 0);
            },
            success: function(ret) {
                if (!cmsCurrentCommand.silent)
                    cmsHideInfoPanel();
                if (ret != 0) {
                    if ($.isFunction(cmsCurrentCommand.success)) {
                        cmsCurrentCommand.success(ret);
                    }
                    cmsAjaxSaveDone();
                    cmsCurrentCommand = null;
                    if (cmsSaveCommands.length > 0) {
                        cmsAjaxSaveProcess();
                    }
                } else {
                    cmsAjaxSaveRepeat(cmsCurrentCommand.tries*cmsSaveRepeatTime);
                }
            },
            error: function() {
                cmsAjaxSaveRepeat(cmsCurrentCommand.tries*cmsSaveRepeatTime);
            }
        });
    }
}

function cmsAjaxSaveDone()
{
    if ((!cmsCurrentCommand) ||
        (cmsCurrentCommand && !cmsCurrentCommand.silent) )
        cmsShowInfoPanel(cmsI18n.cms.saved);

}

function cmsAjaxSaveRepeat(timeout)
{
    if (timeout == null) {
        timeout = cmsSaveRepeatTime;
    }
    if (timeout > cmsSaveMaxRepeatTime) {
        timeout = cmsSaveMaxRepeatTime;
    }
    cmsSaveCommands.unshift(cmsCurrentCommand);
    cmsCurrentCommand = null;

    cmsHideInfoPanel();
    cmsShowInfoPanel(cmsI18n.cms.savingError + ' <span id="cms-info-timer"></span>. <a href="#" id="cms-info-button-repeat">'+cmsI18n.cms.retryNow+'</a>', timeout-1, true);
    $('#cms-info-button-repeat').unbind('click').bind('click',function() {
        cmsHideInfoPanel();
        cmsSaveTimer = false;
        clearTimeout(cmsSaveProcessTimer);
        cmsAjaxSaveProcess();
    });
    clearTimeout(cmsInfoPanelTimeTimer);
    cmsShowInfoPanelTimer(timeout);
    cmsSaveTimer = true;
    cmsSaveProcessTimer = setTimeout("cmsSaveTimer=false;cmsAjaxSaveProcess()", timeout*1000);
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
        cmsInfoPanelTimeTimer = setTimeout("cmsShowInfoPanelTimer("+(timeout-1)+")", 1000);
    }
}

function cmsGetRealBgColor(elem)
{
    if (elem.css('backgroundColor') != 'transparent') {
        return elem.css('backgroundColor');
    } else {
        return cmsGetRealBgColor(elem.parent());
    }
}

function cmsFadeIn(selector, className)
{
    $(selector).removeClass('hover').addClass(className);
}

function cmsFadeOut(selector, className)
{
    var elems = $(selector);
    elems.removeClass('hover');
    elems.each(function(){
        color1 = $(this).css('backgroundColor');
        $(this).removeClass(className);
        color2 = $(this).css('backgroundColor');
        color3 = color2;
        if (color2 == 'transparent') {
            color3 = cmsGetRealBgColor($(this));
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

function cmsClearSelection()
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

function cmsTrim(string)
{
    return string.replace(/(^\s+)|(\s+$)/g, "");
}

function cmsGetAreaNameByPageWidget(pageWidget)
{
    return $(pageWidget).parents('.cms-area').eq(0).attr('id').replace('cms-area-', '');
}

function cmsGetAreaByPageWidget(pageWidget)
{
    return $(pageWidget).parents('.cms-area').eq(0);
}

function cmsGetAreaName(area)
{
    return $(area).attr('id').replace('cms-area-', '');
}

function cmsSanitizeAlias(str)
{
    str = str.replace(/[\s\:\.]/gi, '-')
    while (str.indexOf('--')>-1) {
        str = str.replace(/\-\-/gi, '-');
    }
    return str.replace(/[^0-9A-Za-zА-Яа-я-]*/gi, '');
}

function cmsMakeUrl(alias, oldurl)
{
    var p = oldurl.split('/');
    p[p.length-1] = alias;
    return p.join('/');
}


function cmsAjaxSubmitForm(form, data, hasError, events)
{
    var btn_name = form.attr('rev');
    if (!hasError) {
        // Сохранить юнит
        if (!btn_name) { btn_name = 'save'; }
        var params = form.serialize() + '&' + btn_name +'=1';
        cmsAjaxSave(form.attr('action'), params, form.attr('method'), function(html) {
            // Обновить на странице
            if (events != undefined && $.isFunction(events.onSuccess)) {
                events.onSuccess(html);
            }

            var pageWidgetId = form.attr('rel');
            if (pageWidgetId != undefined) {
                var pageWidget = $('#cms-pagewidget-'+pageWidgetId);
                var widgetId = pageWidget.attr('rev');
                //alert(pagewidget.attr('rev'));
                cmsReloadPageWidget(pageWidgetId, '.cms-pagewidget[rev='+widgetId+']');
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
        if ($(this).find('.cms-pagewidget').length > 0) {
            $(this).find('.cms-empty-area-buttons').remove();
        } else {
            var btn = $('<a class="cms-button w100p" title="'+cmsI18n.cms.addAnotherWidget+'" href="#">'+cmsI18n.cms.addWidget+'</a>')
                .click(function() {
                    cmsPageWidgetAddForm(this);
                    return false;
                });
            $(this).html('').append($('<div class="cms-empty-area-buttons"></div>').append(btn));
        }
    });
}

function cmsPageWidgetAddForm(t)
{
    var pageWidget = $(t).parents('.cms-pagewidget');
    if (pageWidget.length) {
        pageWidget  = pageWidget.eq(0);
        var pageWidgetId = pageWidget.attr('id').replace('cms-pagewidget-','');
    } else {
        var pageWidgetId = '0';
    }
    var area_name = $(t).parents('.cms-area').eq(0).attr('id').replace('cms-area-','');
    pageWidgetAdd = $('#cms-pagewidget-add').clone().attr('id', 'cms-pagewidget-addsplash');
    pageWidgetAdd.find('.cms-btn-pagewidget-create').attr('rel', area_name);
    pageWidgetAdd.find('.cms-btn-pagewidget-create').attr('rev', pageWidgetId);
    cmsOpenDialog(pageWidgetAdd);
}

// =============================================================


function cmsPageWidgetEditForm(t)
{
    var pageWidget = $(t);
    if (pageWidget.hasClass('cms-selected')) {return;}
    cmsFadeIn(pageWidget, 'cms-selected');
    pageWidgetId = pageWidget.attr('id').replace('cms-pagewidget-','');
    widgetClass = pageWidget.attr('rel');
    cmsLoadDialog('/?r=widget/edit&widgetClass='+widgetClass+'&pageWidgetId='+pageWidgetId+'&language='+$.data(document.body, 'language'), {
        pageWidget: pageWidget,
        pageWidgetId: pageWidgetId,
        simpleClose: false,
        width: 830,
        onClose: function() {
            cmsReloadPageWidget(pageWidgetId, '.cms-pagewidget[rev='+pageWidget.attr('rev')+']');
        }
    });
}

function cmsPageWidgetDeleteDialog(widgetId, pageWidgetId, pageId)
{
    $.ajax({
        url: '/?r=widget/getPageWidgetsByWidgetId&pageId='+pageId+'&widgetId='+widgetId+'&language='+$.data(document.body, 'language'),
        pageWidgetId: pageWidgetId,
        widgetId: widgetId,
        pageId: pageId,
        type: 'GET',
        cache: false,
        beforeSend: function() {
            cmsShowInfoPanel(cmsHtmlLoadingImage, 0);
        },
        success: function(html) {
            cmsHideInfoPanel();
            var ids = jQuery.parseJSON(html);
            if (ids.length > 1)
            {
                cmsLoadDialog('/?r=widget/deleteDialog&pageId='+pageId+'&widgetId='+widgetId+'&pageWidgetId='+pageWidgetId+'&language='+$.data(document.body, 'language'));
            }
            else {
                if (confirm(cmsI18n.cms.deleteWidgetWarning))
                {
                    cmsAjaxSave('/?r=widget/delete&pageId='+pageId+'&pageWidgetId[]='+pageWidgetId+'&widgetId='+widgetId+'&language='+$.data(document.body, 'language'), '', 'GET', function(ret) {
                        $('#cms-pagewidget-'+pageWidgetId).remove();
                        cmsAreaEmptyCheck();
                    });
                } else {
                    cmsFadeOut('.cms-selected', 'cms-selected');
                }
            }
        }
    });

}

function cmsPageWidgetSetDialog(pageId, pageWidgetId, widgetId)
{
    cmsLoadDialog('/?r=widget/setDialog&pageId='+pageId+'&widgetId='+widgetId+'&pageWidgetId='+pageWidgetId+'&language='+$.data(document.body, 'language'), {
        onLoad: function() {
            $.topbox.clear();
        }
    });
}

// =============================================================


function cmsPageAddForm()
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


function cmsPageEditForm()
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
                        cmsPageDeleteDialog(null, function() {
                            cmsAjaxSave($('#'+formId).attr('action'), $('#'+formId).serialize()+'&delete=1', 'POST', function(html) {
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

function cmsPageDeleteDialog(pageId, onOneDelete, onChildrenDelete, onCancel)
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
            cmsShowInfoPanel(cmsHtmlLoadingImage, 0);
        },
        success: function(html) {
            cmsHideInfoPanel();
            if (html == '0') {
                if (confirm(cmsI18n.cms.deletePageWarning))
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


function cmsRecordEditForm(id, className, widgetId, gridId)
{
    var dlgId = 'cmsRecordEditForm'+className+'_'+id;
    cmsLoadDialog('/?r=widget/edit&modelClass='+className+'&recordId='+id+'&language='+$.data(document.body, 'language'), {
        simpleClose: false,
        id: dlgId,
        className: 'cmsRecordEditForm-'+className,
        title: cmsI18n.cms.editing,
        gridId: gridId,
        onOpen: function() {
            $('#'+dlgId).find('.field_title input:eq(0)').focus();
        },
        onClose: function() {
            $.fn.yiiGridView.update(gridId);
        }
    });
}

function cmsRecordDelete(id, className, widgetId, gridId)
{
    if (widgetId > 0) {
        // Удаляем юнит
        $.ajax({
            url:'/?r=widget/check&widgetId='+widgetId+'&className='+className+'&pageId='+id+'&language='+$.data(document.body, 'language'),
            cache: false,
            id: id,
            className: className,
            widgetId: widgetId,
            beforeSend: function() {
                cmsShowInfoPanel(cmsHtmlLoadingImage, 0);
            },
            success: function(html) {
                cmsHideInfoPanel();
                if (html.substring(0,2) == '{"') {
                    var ret = jQuery.parseJSON(html);
                    if (ret.page) {
                        if (ret.page.similarToParent) {
                            cmsRecordDeleteConfirm(widgetId, gridId, '&withPage=1');
                        } else {
                            var buttons = {};
                            buttons[cmsI18n.cms.deleteWidgetOnly] = function() {
                                cmsRecordDeleteConfirm(widgetId, gridId);
                                $(this).dialog('close');
                            };
                            buttons[cmsI18n.cms.deleteWidgetAndPage] = function() {
                                cmsRecordDeleteConfirm(widgetId, gridId, '&withPage=1');
                                $(this).dialog('close');
                            };
                            var dlgId = 'WidgetDeleteConform_'+widgetId;
                            var dlg = $('#cms-dialog').clone().attr('id', dlgId).addClass('cms-dialog').appendTo('body');
                            dlg.html('<span class="ui-icon ui-icon-alert" style="float:left; margin:0 10px 100px 0;"></span>'+t(cmsI18n.cms.deleteWidgetRecordConfirm, {'{page}': '(<a target="_blank" href="'+ret.page.url+'">'+ret.page.title+'</a>)'}));
                            dlg.dialog({
                                title: cmsI18n.cms.deletingWidget,
                                modal: true,
                                zIndex: 10000,
                                buttons: buttons,
                                close: function() {
                                    $('#'+dlgId).remove();
                                }
                            });
                        }
                    } else {
                        cmsRecordDeleteConfirm(widgetId, gridId);
                    }
                } else {
                    cmsRecordDeleteConfirm(widgetId, gridId);
                }
            }
        });
    } else {
        // Удаляем просто запись
        if (confirm(cmsI18n.cms.deleteRecordWarning))
        {
            cmsAjaxSave('/?r=records/delete&id='+id+'&className='+className+'&language='+$.data(document.body, 'language'), '', 'GET', function(ret) {
                $.fn.yiiGridView.update(gridId);
            });
        }
    }
}

function cmsRecordDeleteConfirm(widgetId, gridId, data, str)
{
    if (str == undefined) {
        var str = cmsI18n.cms.deleteRecordWarning;
    }
    if (data == undefined) {
        var data = '';
    }
    if (confirm(str))
    {
        cmsAjaxSave('/?r=widget/delete&widgetId='+widgetId+'&pageWidgetId=all'+data+'&language='+$.data(document.body, 'language'), '', 'GET', function(ret) {
            if (gridId !== undefined) {
                $.fn.yiiGridView.update(gridId);
            }
        });
    }
}

function cmsGotoRecordPage(id, className)
{
    $.ajax({
        url:'/?r=records/getUrl&className='+className+'&id='+id+'&language='+$.data(document.body, 'language'),
        cache: false,
        id: id,
        className: className,
        beforeSend: function() {
            cmsShowInfoPanel(cmsHtmlLoadingImage, 0);
        },
        success: function(ret) {
            cmsHideInfoPanel();
            if (ret) {
                location.href = ret;
            }
        }
    });
}
