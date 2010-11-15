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

var pageunit_dragging = false;


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
        $.ajax({
            url: cms_current_command.url,
            data: cms_current_command.data,
            cache: false,
            type: cms_current_command.method,
            beforeSend: function() {
                showInfoPanel(cms_html_loading_image, 0);
            },
            success: function(ret) {
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
    showInfoPanel('Сохранено');
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
    
    showInfoPanel('Ошибка при сохранении. Попытка через <span id="cms-info-timer"></span>. <a href="#" id="cms-info-button-repeat">Повторить сейчас</a>', timeout-1);
    $('#cms-info-button-repeat').click(function() {
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

function showInfoPanel(html, timeout)
{
    clearTimeout(cms_infopanel_timer);
    if (html) {
        $('#cms-info').html(html);
    }
    var width = $('#cms-info').width();
    var body_width = $('body').width();
    $('#cms-info').css('left',Math.round((body_width-width)/2)).slideDown();
    if (timeout == null) {
        timeout = cms_infopanel_timeout;
    }
    if (timeout > 0) {
        cms_infopanel_timer = setTimeout('hideInfoPanel()', timeout*1000);
    }
}

function hideInfoPanel()
{
    $('#cms-info').slideUp();
}

// Панель управления юнитом (создать, удалить, вверх, вниз, редактировать)

function showPageunitPanel(pageunit)
{
    if (!pageunit_dragging) {
        var position = $(pageunit).position();
        var width = $('#cms-pageunit-menu').width();
        var left = position.left-width;
        if (left < 0) {
            left = 0;
        }
        $('#cms-pageunit-menu').prependTo($(pageunit)).css({
            'position': 'absolute',
            'left': left,
            'top': position.top-10
        }).show();
        width = $('#cms-pageunit-menu').width();
        left = position.left-width;
        $('#cms-pageunit-menu').css('left', left);
    }
}

function hidePageunitPanel()
{
    $('#cms-pageunit-menu').hide();
}

function GetOutPageunitPanel()
{
    $('#cms-pageunit-menu').appendTo('body');
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
                    handle: '.ui-tabs-nav'
                });
            }
            if ($.isFunction(options.onComplete)) {
                options.onComplete(this);
            }
        },
        onClosed: function() {
            hideSplash();
            //$('.selected').removeClass('selected');
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
    $('.selected').removeClass('selected');
    $('#fancybox-outer, #fancybox-inner').resizable("destroy");
    $('#fancybox-outer').removeAttr('style');
    $('#fancybox-wrap').draggable("destroy");
    $.fancybox.resize();
    $.fancybox.close();
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
        return confirm('Вы действительно хотите удалить? Данные будут удалены безвозвратно.');
    });
    f.find('input').bind('keydown', 'ctrl+return', function() {
        $(this).after('<input class="submit" type="hidden" name="save" value="save" />');
        $(this).parents('form').submit();
    });

}

function ajaxSubmitForm(form, data, hasError)
{
    var btn_name = form.attr('rev');
    if (!hasError) {
        // Сохранить юнит
        if (btn_name == undefined) { btn_name = 'save'; }
        var params = form.serialize() + '&' + btn_name +'=1';
        ajaxSave(form.attr('action'), params, form.attr('method'), function(html) {
            // Обновить на странице
            var pageunit_id = form.attr('rel');
            if (pageunit_id != undefined) {
                var pageunit = $('#cms-pageunit-'+pageunit_id);
                GetOutPageunitPanel();
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
                location.reload();
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

    }
    return false;
}

// =============================================================


function CmsPageunitHovering()
{
    $('.cms-pageunit').unbind('mouseenter').unbind('mouseleave').mouseenter(function() {
        $(this).addClass('hover');
        showPageunitPanel(this);
    }).mouseleave(function() {
        $(this).removeClass('hover');
        hidePageunitPanel(this);
    });    
}

function CmsAreaEmptyCheck()
{
    $('.cms-area').each(function() {
        if ($(this).find('.cms-pageunit').length > 0) {
            $(this).find('.cms-empty-area-buttons').remove();
        } else {
            $(this).html('<div class="cms-empty-area-buttons"><a class="cms-button-medium cms-button-add cms-btn-pageunit-add" title="Добавить еще один блок" href="#"></a></div>');
        }
    });    
}

function CmsPageunitDisabling()
{
    CmsAreaEmptyCheck();
//    $('.cms-pageunit a').unbind('click').click(function(){
//        return false;
//    });    
}

function CmsPageunitEnabling()
{
//    $('.cms-pageunit a').unbind('click');
}

function CmsPageunitEditing()
{
    $('.cms-pageunit').unbind('dblclick').dblclick(function() {
        clearSelection();
        pageunitEditForm(this);
        return false;
    })    
}

// =============================================================


function pageunitEditForm(t)
{
    var pageunit = $(t);
    pageunit.addClass('selected');
    hidePageunitPanel(t);
    pageunit_id = pageunit.attr('id').replace('cms-pageunit-','');
    unit_type = pageunit.attr('rel');
    $.ajax({
        url:'/?r=page/unitForm&unit_type='+unit_type+'&pageunit_id='+pageunit_id,
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
                    $('#Unit_title').get(0).focus();
                },
                onClose: function() {
                    GetOutPageunitPanel();
                    updatePageunit(pageunit_id, '.cms-pageunit[rev='+pageunit.attr('rev')+']');
                }
            });
        }
    });    
}

function pageunitDeleteDialog(unit_id, pageunit_id, page_id)
{
    $.ajax({
        url: '/?r=page/getPageunitsByUnit&unit_id='+unit_id,
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
                    url: '/?r=page/unitDeleteDialog&id='+page_id+'&unit_id='+unit_id+'&pageunit_id='+pageunit_id,
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
                if (confirm('Вы действительно хотите удалить эту запись? Удаляемая информация будет безвозвратно потеряна.'))
                {
                    ajaxSave('/?r=page/unitDelete&pageunit_id[]='+pageunit_id+'&unit_id='+unit_id, '', 'GET', function(ret) {
                        GetOutPageunitPanel();
                        $('#cms-pageunit-'+pageunit_id).remove();
                        CmsAreaEmptyCheck();
                    });
                } else {
                    $('.selected').removeClass('selected');
                }
            }
        }
    });

}

function updatePageunit(pageunit_id, selector, onSuccess)
{
    var page_id = $('body').attr('rel');
    $.ajax({
        url: '/?r=page/unitView&pageunit_id='+pageunit_id+'&id='+page_id,
        async: false,
        cache: false,
        success: function(html) {
            $(selector).html(html);
            CmsPageunitDisabling();
            if ($.isFunction(onSuccess)) {
                onSuccess(html);
            }
        }
    });
}

function pageunitSetDialog(page_id, pageunit_id, unit_id)
{
    $.ajax({
        url: '/?r=page/unitSetDialog&id='+page_id+'&unit_id='+unit_id+'&pageunit_id='+pageunit_id,
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
    hidePageunitPanel();
    $.ajax({
        url:'/?r=page/pageAdd&id='+page_id,
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
    hidePageunitPanel();
    $.ajax({
        url:'/?r=page/pageForm&id='+page_id,
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
        url:'/?r=page/hasChildren&id='+page_id,
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
                if (confirm('Вы действительно хотите удалить страницу? Данные на странице будут удалены безвозвратно.'))
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
                    url:'/?r=page/pageDeleteDialog&id='+page_id,
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
        url:'/?r=page/unitForm&class_name='+class_name+'&id='+id,
        cache: false,
        id: id,
        class_name: class_name,
        beforeSend: function() {
            showInfoPanel(cms_html_loading_image, 0);
        },
        success: function(html) {
            hideInfoPanel();
            var dlg_id = 'recordEditForm'+class_name+'_'+id;
            var dlg_class = 'recordEditForm-'+class_name;
            var dlg = $('#cms-dialog').clone().attr('id', dlg_id).addClass(dlg_class).addClass('cms-dialog').appendTo('body');
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
                title: 'Редактирование',
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
            });
        }
    });

}

function recordDelete(id, class_name, unit_id, grid_id)
{
    if (unit_id) {
        // Удаляем юнит
        $.ajax({
            url:'/?r=page/unitCheck&unit_id='+unit_id+'&class_name='+class_name+'&id='+id,
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
                            var dlg_id = 'UnitDeleteConform_'+unit_id;
                            var dlg = $('#cms-dialog').clone().attr('id', dlg_id).addClass('cms-dialog').appendTo('body');
                            dlg.html('<span class="ui-icon ui-icon-alert" style="float:left; margin:0 10px 100px 0;"></span>На странице (<a target="_blank" href="'+ret.page.url+'">'+ret.page.title+'</a>), где размещен удаляемый блок, также присутствуют какие-то другие информационные блоки. Возможно, вы добавили что-то самостоятельно на указанную страницу. Что делать?');
                            dlg.dialog({
                                title: 'Удаление блока',
                                modal: true,
                                zIndex: 10000,
                                buttons: {
                                    "Удалить только блок": function() {
                                        recordDeleteConfirm(unit_id, grid_id);
                                        $(this).dialog('close');
                                    },
                                    "Удалить и блок, и страницу": function() {
                                        recordDeleteConfirm(unit_id, grid_id, '&with_page=1');
                                        $(this).dialog('close');
                                    }
                                },
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
        if (confirm('Вы действительно хотите удалить эту запись? Удаляемая информация будет безвозвратно потеряна.'))
        {
            ajaxSave('/?r=records/delete&id='+id+'&class_name='+class_name, '', 'GET', function(ret) {
                $.fn.yiiGridView.update(grid_id);
            });
        }
    }
}

function recordDeleteConfirm(unit_id, grid_id, data, str)
{
    if (str == undefined) {
        var str = 'Вы действительно хотите удалить эту запись? Удаляемая информация будет безвозвратно потеряна.';
    }
    if (confirm(str))
    {
        ajaxSave('/?r=page/unitDelete&unit_id='+unit_id+'&pageunit_id=all'+data, '', 'GET', function(ret) {
            $.fn.yiiGridView.update(grid_id);
        });
    }
}

function gotoRecordPage(id, class_name)
{
    $.ajax({
        url:'/?r=records/getUrl&class_name='+class_name+'&id='+id,
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