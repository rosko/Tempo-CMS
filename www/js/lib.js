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
    var url = '/?r=page/areaSort&page_id='+page_id;
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
        var width = $('#cms-pageunit-menu').width();
        var left = position.left-width;
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

function showSplash(t, onComplete, with_confirm, onClose, resizable, draggable)
{
    if (draggable == null) {
        draggable = true;
    }
    $.fancybox(t, {
        showNavArrows: false,
        autoDimensions: true,
        autoScale: true,
        hideOnOverlayClick: !with_confirm,
        onComplete: function() {
            $(document).unbind('keydown.fb');
            $.fancybox.resize();
            if (resizable) {
                $('#fancybox-outer').resizable({
                    alsoResize: '#fancybox-inner'
                });
            }
            if (draggable) {
                $('#fancybox-wrap').draggable();
            }
            if ($.isFunction(onComplete)) {
                onComplete(this);
            }
        },
        onClosed: function() {
            hideSplash();
            //$('.selected').removeClass('selected');
            if ($.isFunction(onClose)) {
                onClose(this);
            }
        }
    });
    
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


function AjaxifyForm(container, f, onSubmit, onSave)
{
    f.attr('target', container);
    f.submit(function(){
        ajaxSave(f.attr('action'), f.serialize(), f.attr('method'), function(html) {
            var container = f.attr('target');
            var rel = f.attr('rel');
            var btn_name = $('.submit:hidden').eq(0).attr('name');
            if (html.substring(0,2) != '{"') {
                if ((btn_name != 'apply') && ($(html).find('form').eq(0).find('.errorMessage').length == 0))
                {
                    hideSplash();
                } else {
                    $(container).html(html);
                    $(container).find('form').eq(0).attr('rel', rel);
                    AjaxifyForm(container, $(container).find('form').eq(0), onSubmit,onSave);
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
            $(this).html('<div class="cms-empty-area-buttons"><a class="cms-button-medium cms-button-add cms-btn-pagenit-add" title="Добавить еще один блок" href="#"></a></div>');
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
        url:'/?r=page/pageunitForm&unit_type='+unit_type+'&pageunit_id='+pageunit_id,
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
            AjaxifyForm('#cms-pageunit-edit', $('#cms-pageunit-edit').find('form').eq(0), function(f) {
                var pageunit_id = f.attr('rel');
                var pageunit = $('#cms-pageunit-'+pageunit_id);
                GetOutPageunitPanel();
                var unit_id = pageunit.attr('rev');
                //alert(pageunit.attr('rev'));
                updatePageunit(pageunit_id, '.cms-pageunit[rev='+unit_id+']');
                updatePageunit(pageunit_id, '.cms-pageunit[rev='+unit_id+']');
            });
            showSplash($('#cms-pageunit-edit'), function() {
                $('#Unit_title').get(0).focus();
            }, true, function() {
                GetOutPageunitPanel();
                updatePageunit(pageunit_id, '.cms-pageunit[rev='+pageunit.attr('rev')+']');
            }, false, false);
        }
    });    
}

function updatePageunit(pageunit_id, selector)
{
    var page_id = $('body').attr('rel');
    $.ajax({
        url: '/?r=page/pageunitView&pageunit_id='+pageunit_id+'&id='+page_id,
        async: false,
        cache: false,
        success: function(html) {
            $(selector).html(html);
            CmsPageunitDisabling();
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
            AjaxifyForm('#cms-page-edit', $('#cms-page-edit').find('form').eq(0), function(f) {
            }, function (html) {
                if (html.substring(0,2) == '{"') {
                    var ret = jQuery.parseJSON(html);
                    if (ret) {
                      location.href = ret.url;
                    }    
                }
            });
            showSplash($('#cms-page-edit'), function() {
                $('#Page_title').get(0).focus();
                $('#Page_parent_id').val($('body').attr('rel'));
                $('#Page_parent_id_title').val($.data(document.body, 'title'));
            }, true);
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
            AjaxifyForm('#cms-page-edit', $('#cms-page-edit').find('form').eq(0), function(f) {
            }, function (html) {
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
            showSplash($('#cms-page-edit'), function() {
                $('#Page_title').get(0).focus();
                $('input[name=deletepage]').unbind('click').click(function() {
                    pageDeleteConfirm(null, function() {
                        $('#cms-page-edit').find('form').eq(0).append('<input class="submit" type="hidden" name="delete" value="delete" />').submit();
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
            }, true);
        }
    });    
}

function pageDeleteConfirm(page_id, onOneDelete, onChildrenDelete, onCancel)
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
                    url:'/?r=page/pageDeleteConfirm&id='+page_id,
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
                        showSplash($('#cms-page-delete'), null, null, function() {
                            if ($.isFunction(onCancel)) {
                                onCancel(this);
                            }                                        
                        });
                    }
                });
            }
        }
    });    
}
