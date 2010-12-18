function t(message, params)
{
    for (x in params)
    {
        message = message.replace(x, params[x]);
    }
    return message;
}

// =============================================================

// Картинка изображающая процесс загрузки
var cms_html_loading_image = '<img src="/images/ajax-loader.gif" />';
// Сколько секунд отображается всплывающая инфо-панель
var cms_infopanel_timeout = 3;
var cms_infopanel_timer = null;
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

function CmsCommand(url, data, method, success)
{
    this.url = url;
    this.data = data;
    this.method = method;
    this.success = success;
}

function ajaxSaveArea(area, name, page_id, add_params)
{
    var url = '/?r=page/unitMove&page_id='+page_id;
    var params = $(area).sortable('serialize');
    var data = 'area='+name+'&'+params
    if (add_params) {
        data = add_params+'&'+data;
    }
    ajaxSave(url, data);
}

function ajaxSave(url, data, method, success)
{
    cms_save_commands.push(new CmsCommand(url, data, method, success));
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
                showInfoPanel(cms_html_loading_image, 0);
            },
            success: function(ret) {
                hideInfoPanel();
                if (ret != 0) {
                    if ($.isFunction(cms_current_command.success)) {
                        cms_current_command.success(ret);
                    }
                    ajaxSaveDone();
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
    showInfoPanel(i18n.cms.saved);
    cms_current_command = null;
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
    
    hideInfoPanel();
    showInfoPanel(i18n.cms.savingError + ' <span id="cms-info-timer"></span>. <a href="#" id="cms-info-button-repeat">'+i18n.cms.retryNow+'</a>', timeout-1, true);
    $('#cms-info-button-repeat').unbind('click').bind('click',function() {
        hideInfoPanel();
        cms_save_timer = false;
        clearTimeout(cms_saveprocess_timer);
        ajaxSaveProcess();
    });
    clearTimeout(cms_infopanel_time_timer);
    showInfoPanelTimer(timeout);
    cms_save_timer = true;
    cms_saveprocess_timer = setTimeout("cms_save_timer=false;ajaxSaveProcess()", timeout*1000);
}

function showInfoPanelTimer(timeout)
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
        cms_infopanel_time_timer = setTimeout("showInfoPanelTimer("+(timeout-1)+")", 1000);
    }
}

// =============================================================
// Функции для отображение разных уведомление и диалоговых окон

// Уведомляющая надпись

function showInfoPanel(html, timeout, error)
{
    clearTimeout(cms_infopanel_timer);
    var t = 'message';
    if (error) {
        t = 'error';
    }
    if (timeout == null) {
        timeout = cms_infopanel_timeout;
    }
    notify(html, {
        type: t,
        disappearTime: timeout
    });
    if (timeout > 0) {
        cms_infopanel_timer = setTimeout('hideInfoPanel()', timeout*1000);
    }
}

function hideInfoPanel()
{
    removeLastNotification();
}

function notify(message, opts)
{
    if (opts == null) {
        opts = {};
    }
    if (opts.disappearTime <= 0) {
        opts.permanent = true;
    }
    $('#cms-notification').jnotifyAddMessage({
        text: message,
        type: opts.type,
        showIcon: opts.showIcon,
        permanent: opts.permanent,
        disappearTime: opts.disappearTime*1000
    });
}

function removeLastNotification()
{
    var obj = $('#cms-notification .jnotify-item:last');
    obj.animate({ opacity: '0' }, 600, function() {
        obj.parent().animate({ height: '0px' }, 300,
              function() {
                  obj.parent().remove();
                  // IEsucks
                  if (navigator.userAgent.match(/MSIE (\d+\.\d+);/)) {
                      //http://groups.google.com/group/jquery-dev/browse_thread/thread/ba38e6474e3e9a41
                      obj.parent().parent().removeClass('IEsucks');
                  }
                  // -------
        });
    });
}

function getLastNotification()
{
    return $('#cms-notification .jnotify-item:last').text();
}

// Диалоговое окно

function showSplash(elem, options) {
    if (options == null) {
        options = {};
    }

    $.fancybox(elem, {
        showNavArrows: false,
        autoDimensions: true,
        autoScale: true,
        centerOnScroll: options.centerOnScroll,
        hideOnOverlayClick: !options.withConfirm,
        onComplete: function() {
            $(document).unbind('keydown.fb');
            $.fancybox.resize();
            if (options.resizable) {
                $('#fancybox-outer').resizable({
                    alsoResize: '#fancybox-inner'
                });
            }
            if (options.draggable) {
                $('#fancybox-wrap').draggable({
                    handle: '.ui-tabs-nav, .cms-caption'
                });
            }
            if ($.isFunction(options.onComplete)) {
                options.onComplete(this);
            }
        },
        onClosed: function() {
            hideSplash();
            elem.width('auto').height('auto').html('');
            if ($.isFunction(options.onClose)) {
                options.onClose(this);
            }
        }
    });

}

function resizeSplash()
{
    $.fancybox.resize();
}

function hideSplash()
{
    fadeOut('.selected', 'selected');
    $('#fancybox-outer, #fancybox-inner').resizable("destroy");
    $('#fancybox-outer').removeAttr('style');
    $('#fancybox-wrap').draggable("destroy");
    $.fancybox.resize();
    $.fancybox.close();
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
        $(this).css('backgroundColor', color1).animate({backgroundColor: color2}, 1000);
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

function getAreaNameByPageunit(pageunit)
{
    return $(pageunit).parents('.cms-area').eq(0).attr('id').replace('cms-area-', '');
}

function getAreaByPageunit(pageunit)
{
    return $(pageunit).parents('.cms-area').eq(0);
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

// =============================================================


function AjaxifyForm(container, f, onSubmit, onSave, onClose, validate)
{
    f.attr('target', container);
    f.submit(function(){
        ajaxSave(f.attr('action'), f.serialize(), f.attr('method'), function(html) {
            var container = f.attr('target');
            var rel = f.attr('rel');
            var btn_name = $('.submit:hidden').eq(0).attr('name');
            if (html.substring(0,2) != '{"') {
                if (!validate) {
                    var errsCount = $(html).find('form').eq(0).find('.errorMessage').length;
                } else {
                    var errsCount = 0;
                }
                if ((btn_name != 'apply') && (errsCount == 0))
                {
                    if ($.isFunction(onClose)) {
                        onClose(html);
                    } else
                        hideSplash();
                } else {
                    if (!validate) {
                        $(container).html(html);
                    }
                    $(container).find('form').eq(0).attr('rel', rel);
                    AjaxifyForm(container, $(container).find('form').eq(0), onSubmit, onSave, onClose, validate);
                }
            }
            if ($.isFunction(onSave)) {
                onSave(html);
            }
        });
        if ($.isFunction(onSubmit)) {
            onSubmit(f);
        }
        return false;        
    });
    f.find(':submit').click(function(){
        $(this).after('<input class="submit" type="hidden" name="'+$(this).attr('name')+'" value="'+$(this).val()+'" />');
    });
    f.find('input[name=delete]').click(function() {
        return confirm(i18n.cms.deleteWarning);
    });
    f.find('input').bind('keydown', 'ctrl+return', function() {
        $(this).after('<input class="submit" type="hidden" name="save" value="save" />');
        $(this).parents('form').submit();
    });

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

            var pageunit_id = form.attr('rel');
            if (pageunit_id != undefined) {
                var pageunit = $('#cms-pageunit-'+pageunit_id);
                var unit_id = pageunit.attr('rev');
                //alert(pageunit.attr('rev'));
                updatePageunit(pageunit_id, '.cms-pageunit[rev='+unit_id+']');
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
            }
            if (btn_name == 'refresh') {
                location.href = '/?r=page/view&id='+$('body').attr('rel')+'&language='+$.data(document.body, 'language');
            } else if (btn_name != 'apply') {
                if ($(form).parents('#fancybox-wrap').length) {
                    $(form).remove();
                    hideSplash();
                } else {
                    var dlg = $(form).parents('.cms-dialog');
                    $(form).remove();
                    $(dlg).dialog('close');
                }
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


function CmsAreaEmptyCheck()
{
    $('.cms-area').each(function() {
        if ($(this).find('.cms-pageunit').length > 0) {
            $(this).find('.cms-empty-area-buttons').remove();
        } else {
            var btn = $('<a class="cms-button w100p" title="'+i18n.cms.addAnotherUnit+'" href="#">'+i18n.cms.addUnit+'</a>')
                .click(function() {
                    pageunitAddForm(this);
                    return false;
                });
            $(this).html('').append($('<div class="cms-empty-area-buttons"></div>').append(btn));
        }
    });    
}

function pageunitAddForm(t)
{
    var pageunit = $(t).parents('.cms-pageunit');
    if (pageunit.length) {
        pageunit  = pageunit.eq(0);
        var pageunit_id = pageunit.attr('id').replace('cms-pageunit-','');
    } else {
        var pageunit_id = '0';
    }
    var area_name = $(t).parents('.cms-area').eq(0).attr('id').replace('cms-area-','');
    pageunitadd = $('#cms-pageunit-add').clone().attr('id', 'cms-pageunit-addsplash');
    pageunitadd.find('.cms-btn-pageunit-create').attr('rel', area_name);
    pageunitadd.find('.cms-btn-pageunit-create').attr('rev', pageunit_id);
    showSplash(pageunitadd);
}

// =============================================================


function pageunitEditForm(t)
{
    var pageunit = $(t);
    if (pageunit.hasClass('selected')) { return; }
    fadeIn(pageunit, 'selected');
    pageunit_id = pageunit.attr('id').replace('cms-pageunit-','');
    unit_type = pageunit.attr('rel');
    $.ajax({
        url:'/?r=page/unitForm&unit_type='+unit_type+'&pageunit_id='+pageunit_id+'&language='+$.data(document.body, 'language'),
        cache: false,
        pageunit: pageunit,
        pageunit_id: pageunit_id,
        beforeSend: function() {
            showInfoPanel(cms_html_loading_image, 0);                    
        },
        success: function(html) {
            hideInfoPanel();
            $('#cms-pageunit-edit').html(html);
            $('#cms-pageunit-edit').find('form').eq(0).attr('rel', pageunit_id);
            $('#cms-pageunit-edit').find('form').find('input[type="submit"]').click(function() {
                $(this).parents('form').attr('rev', $(this).attr('name'));
            });

            showSplash($('#cms-pageunit-edit'), {
                resizable: true,
                draggable: true,
                withConfirm: true,
                onComplete: function() {
                    if ($('#Unit_title').length)
                        $('#Unit_title').get(0).focus();
                },
                onClose: function() {
                    $('#cms-pageunit-edit').html('');
                    updatePageunit(pageunit_id, '.cms-pageunit[rev='+pageunit.attr('rev')+']');
                }
            });
        }
    });    
}

function pageunitDeleteDialog(unit_id, pageunit_id, page_id)
{
    $.ajax({
        url: '/?r=page/getPageunitsByUnit&unit_id='+unit_id+'&language='+$.data(document.body, 'language'),
        pageunit_id: pageunit_id,
        unit_id: unit_id,
        page_id: page_id,
        type: 'GET',
        cache: false,
        beforeSend: function() {
            showInfoPanel(cms_html_loading_image, 0);
        },
        success: function(html) {
            hideInfoPanel();
            var ids = jQuery.parseJSON(html);
            if (ids.length > 1)
            {
                $.ajax({
                    url: '/?r=page/unitDeleteDialog&id='+page_id+'&unit_id='+unit_id+'&pageunit_id='+pageunit_id+'&language='+$.data(document.body, 'language'),
                    type: 'GET',
                    cache: false,
                    beforeSend: function() {
                        showInfoPanel(cms_html_loading_image, 0);
                    },
                    success: function(html) {
                        hideInfoPanel();
                        $('#cms-pageunit-delete').html(html);
                        showSplash($('#cms-pageunit-delete'));
                    }
                });
            }
            else {
                if (confirm(i18n.cms.deleteUnitWarning))
                {
                    ajaxSave('/?r=page/unitDelete&pageunit_id[]='+pageunit_id+'&unit_id='+unit_id+'&language='+$.data(document.body, 'language'), '', 'GET', function(ret) {
                        $('#cms-pageunit-'+pageunit_id).remove();
                        CmsAreaEmptyCheck();
                    });
                } else {
                    fadeOut('.selected', 'selected');
                }
            }
        }
    });

}

function updatePageunit(pageunit_id, selector, onSuccess)
{
    var page_id = $('body').attr('rel');
    var language = $('body').data('language');
    $.ajax({
        url: '/?r=page/unitView&pageunit_id='+pageunit_id+'&id='+page_id+'&language='+language,
        async: false,
        cache: false,
        success: function(html) {
            $(selector).html(html);
            CmsAreaEmptyCheck();
            if ($.isFunction(onSuccess)) {
                onSuccess(html);
            }
        }
    });
}

function pageunitSetDialog(page_id, pageunit_id, unit_id)
{
    $.ajax({
        url: '/?r=page/unitSetDialog&id='+page_id+'&unit_id='+unit_id+'&pageunit_id='+pageunit_id+'&language='+$.data(document.body, 'language'),
        type: 'GET',
        cache: false,
        beforeSend: function() {
            showInfoPanel(cms_html_loading_image, 0);
        },
        success: function(html) {
            hideInfoPanel();
            $('#cms-pageunit-set').html(html);
            showSplash($('#cms-pageunit-set'));
        }
    });

}

// =============================================================


function pageAddForm()
{
    var page_id = $('body').attr('rel');
    $.ajax({
        url:'/?r=page/pageAdd&id='+page_id+'&language='+$.data(document.body, 'language'),
        cache: false,
        beforeSend: function() {
            showInfoPanel(cms_html_loading_image, 0);                    
        },
        success: function(html) {
            hideInfoPanel();
            $('#cms-page-edit').html(html);
            $('#cms-page-edit').find('form').find('input[type="submit"]').click(function() {
                $(this).parents('form').attr('rev', $(this).attr('name'));
            });

            showSplash($('#cms-page-edit'), {
                draggable: true,
                resizable: true,
                withConfirm: true,
                onComplete: function() {
                    $('#Page_title').get(0).focus();
                    $('#Page_parent_id').val($('body').attr('rel'));
                    $('#Page_parent_id_title').val($.data(document.body, 'title'));
                }
            });
        }
    });    

}


function pageEditForm()
{
    var page_id = $('body').attr('rel');
    $.ajax({
        url:'/?r=page/pageForm&id='+page_id+'&language='+$.data(document.body, 'language'),
        cache: false,
        beforeSend: function() {
            showInfoPanel(cms_html_loading_image, 0);                    
        },
        success: function(html) {
            hideInfoPanel();
            $('#cms-page-edit').html(html);
            $('#cms-page-edit').find('form').find('input[type="submit"]').click(function() {
                $(this).parents('form').attr('rev', $(this).attr('name'));
            });

            showSplash($('#cms-page-edit'), {
                withConfirm: true,
                onComplete: function() {
                    $('#Page_title').get(0).focus();
                    $('input[name=deletepage]').unbind('click').click(function() {
                        pageDeleteDialog(null, function() {
                            ajaxSave($('#cms-page-edit').find('form:eq(0)').attr('action'), $('#cms-page-edit').find('form:eq(0)').serialize()+'&delete=1', 'POST', function(html) {
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

function pageDeleteDialog(page_id, onOneDelete, onChildrenDelete, onCancel)
{
    if (!page_id) {
        page_id = $('body').attr('rel');
    }
    $.ajax({
        url:'/?r=page/hasChildren&id='+page_id+'&language='+$.data(document.body, 'language'),
        cache: false,
        page_id: page_id,
        onChildrenDelete: onChildrenDelete,
        onCancel: onCancel,
        beforeSend: function() {
            showInfoPanel(cms_html_loading_image, 0);                    
        },
        success: function(html) {
            hideInfoPanel();
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
                $.ajax({
                    url:'/?r=page/pageDeleteDialog&id='+page_id+'&language='+$.data(document.body, 'language'),
                    cache: false,
                    onChildrenDelete: onChildrenDelete,
                    onCancel: onCancel,
                    page_id: page_id,
                    beforeSend: function() {
                        showInfoPanel(cms_html_loading_image, 0);                    
                    },
                    success: function(html) {
                        hideInfoPanel();
                        $('#cms-page-delete').html(html);
                        AjaxifyForm('#cms-page-delete', $('#cms-page-delete').find('form').eq(0), function(f) {
                        }, function (html) {
                            // В функции должны быть описаны действия после уже совершенного удаления
                            if ($.isFunction(onChildrenDelete)) {
                                onChildrenDelete(html);
                            }                    
                        });
                        showSplash($('#cms-page-delete'), {
                            onClose: function() {
                                if ($.isFunction(onCancel)) {
                                    onCancel(this);
                                }
                            }
                        });
                    }
                });
            }
        }
    });    
}

// =============================================================


function recordEditForm(id, class_name, unit_id, grid_id)
{
    $.ajax({
        url:'/?r=page/unitForm&class_name='+class_name+'&id='+id+'&language='+$.data(document.body, 'language'),
        cache: false,
        id: id,
        class_name: class_name,
        beforeSend: function() {
            showInfoPanel(cms_html_loading_image, 0);
        },
        success: function(html) {
            hideInfoPanel();
            var dlg_id = 'recordEditForm'+class_name+'_'+id;
            if ($('#'+dlg_id).length) { return; }
            var dlg_class = 'recordEditForm-'+class_name;
            var dlg = $('#cms-dialog').clone().attr('id', dlg_id).addClass(dlg_class).addClass('cms-dialog').appendTo('body').hide();
            dlg.html(html);
            if (grid_id !== undefined) {
                dlg.find('form').data('grid_id', grid_id);
            }
            dlg.find('form').find('input[type="submit"]').click(function() {
                $(this).parents('form').attr('rev', $(this).attr('name'));
            });

            var height = $(window).height()-100;
            var width = $(window).width()-200;
            dlg.dialog({
                title: i18n.cms.editing,
                resizable: true,
                draggable: true,
                maxHeight: height,
                maxWidth: width,
                height: height,
                width: width,
                zIndex: 10000,
                modal:true,
                closeOnEscape: false,
                open: function(event, ui) {
                    $('#'+dlg_id).find('label[for="Unit_title"]:eq(0)').next().focus();
                },
                close: function() {
                    $('#'+dlg_id).remove();
                }
            }).show();
        }
    });

}

function recordDelete(id, class_name, unit_id, grid_id)
{
    if (unit_id) {
        // Удаляем юнит
        $.ajax({
            url:'/?r=page/unitCheck&unit_id='+unit_id+'&class_name='+class_name+'&id='+id+'&language='+$.data(document.body, 'language'),
            cache: false,
            id: id,
            class_name: class_name,
            unit_id: unit_id,
            beforeSend: function() {
                showInfoPanel(cms_html_loading_image, 0);
            },
            success: function(html) {
                hideInfoPanel();
                if (html.substring(0,2) == '{"') {
                    var ret = jQuery.parseJSON(html);
                    if (ret.page) {
                        if (ret.page.similarToParent) {
                            recordDeleteConfirm(unit_id, grid_id, '&with_page=1');
                        } else {
                            var buttons = {};
                            buttons[i18n.cms.deleteUnitOnly] = function() {
                                recordDeleteConfirm(unit_id, grid_id);
                                $(this).dialog('close');
                            };
                            buttons[i18n.cms.deleteUnitAndPage] = function() {
                                recordDeleteConfirm(unit_id, grid_id, '&with_page=1');
                                $(this).dialog('close');
                            };
                            var dlg_id = 'UnitDeleteConform_'+unit_id;
                            var dlg = $('#cms-dialog').clone().attr('id', dlg_id).addClass('cms-dialog').appendTo('body');
                            dlg.html('<span class="ui-icon ui-icon-alert" style="float:left; margin:0 10px 100px 0;"></span>'+t(i18n.cms.deleteUnitRecordConfirm, {'{page}': '(<a target="_blank" href="'+ret.page.url+'">'+ret.page.title+'</a>)'}));
                            dlg.dialog({
                                title: i18n.cms.deletingUnit,
                                modal: true,
                                zIndex: 10000,
                                buttons: buttons,
                                close: function() {
                                    $('#'+dlg_id).remove();
                                }
                            });
                        }
                    } else {
                        recordDeleteConfirm(unit_id, grid_id);
                    }
                } else {
                    recordDeleteConfirm(unit_id, grid_id);
                }
            }
        });
    } else {
        // Удаляем просто запись
        if (confirm(i18n.cms.deleteRecordWarning))
        {
            ajaxSave('/?r=records/delete&id='+id+'&class_name='+class_name+'&language='+$.data(document.body, 'language'), '', 'GET', function(ret) {
                $.fn.yiiGridView.update(grid_id);
            });
        }
    }
}

function recordDeleteConfirm(unit_id, grid_id, data, str)
{
    if (str == undefined) {
        var str = i18n.cms.deleteRecordWarning;
    }
    if (confirm(str))
    {
        ajaxSave('/?r=page/unitDelete&unit_id='+unit_id+'&pageunit_id=all'+data+'&language='+$.data(document.body, 'language'), '', 'GET', function(ret) {
            $.fn.yiiGridView.update(grid_id);
        });
    }
}

function gotoRecordPage(id, class_name)
{
    $.ajax({
        url:'/?r=records/getUrl&class_name='+class_name+'&id='+id+'&language='+$.data(document.body, 'language'),
        cache: false,
        id: id,
        class_name: class_name,
        beforeSend: function() {
            showInfoPanel(cms_html_loading_image, 0);
        },
        success: function(ret) {
            hideInfoPanel();
            if (ret) {
                location.href = ret;
            }
        }
    });
}

// =============================================================

function loadDialog(opts)
{
    if (opts == undefined) return false;
    $.ajax({
        url:opts.url,
        cache: false,
        opts: opts,
        beforeSend: function() {
            showInfoPanel(cms_html_loading_image, 0);
        },
        success: function(html) {
            hideInfoPanel();
            if ($.isFunction(opts.onLoad)) {
                opts.onLoad(html);
            }
            var dlg = $('#cms-dialog').clone()
                .attr('id', opts.id)
                .addClass(opts.className)
                .addClass('cms-dialog')
                .appendTo('body');
            dlg.html(html);
            dlg.find('form').find('input[type="submit"]').click(function() {
                $(this).parents('form').attr('rev', $(this).attr('name'));
            });
            dlg.find('form').submit(function(){
                if ($.isFunction(opts.onSubmit)) {
                    opts.onSubmit(this);
                }
                ajaxSubmitForm($(this), null, null, {
                    onSuccess: function(html) {
                        if ($.isFunction(opts.onSave)) {
                            opts.onSave(html);
                        }
                    }
                });
                return false;
            });

            var height = $(window).height()-100;
            var width = $(window).width()-200;
            dlg.dialog({
                title: opts.title,
                resizable: opts.resizable,
                draggable: opts.draggable,
                height: opts.height,
                width: opts.width,
                zIndex: 10000,
                modal:true,
                closeOnEscape: false,
                open: function(event, ui) {
                    $('#'+opts.id).find('input,textarea,select').eq(0).focus();
                    if ($.isFunction(opts.onOpen)) {
                        opts.onOpen(event, ui);
                    }
                },
                close: function() {
                    if ($.isFunction(opts.onClose)) {
                        opts.onClose();
                    }
                    $('#'+opts.id).html('');
                    $('#'+opts.id).remove();
                }
            });
        }
    });

}