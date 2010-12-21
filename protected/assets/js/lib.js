function t(message, params)
{
    for (x in params)
    {
        message = message.replace(x, params[x]);
    }
    return message;
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

function updatePageunit(pageunit_id, selector, onSuccess, data)
{
    var page_id = $('body').attr('rel');
    var language = $('body').data('language');
    var ls = getLocationSearch();
    if (data == undefined) { data = ''} else {
        if (ls)
            data = '&'+data;
    }
    $.ajax({
        url: '/?r=page/unitView&pageunit_id='+pageunit_id+'&id='+page_id+'&language='+language,
        data: ls+data,
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

function getLocationSearch()
{
    var ret = window.location.search.substring(1);
    var arr = ret.split('&');
    var params = new Array();
    for (i in arr) {
        var pos = arr[i].indexOf('=');
        if (pos > 0) {
            key = arr[i].substr(0,pos);
            val = arr[i].substr(pos+1);
            if (key != 'r') {
                params.push(arr[i]);
            }
        }
    }
    return params.join('&');
}

function addToLocationHash(str, process, delvar)
{
    if (location.hash == '' || location.hash == '#' || location.hash == '#.') {
        setLocationHash(str);
    } else {
        if (location.hash.indexOf(str)==-1 && !delvar) { return true; }

        var params = {}
        var arr = location.hash.substr(1).split('&');
        for (i in arr) {
            var pos = arr[i].indexOf('=');
            if (pos > 0) {
                key = arr[i].substr(0,pos);
                val = arr[i].substr(pos+1);
                if (key != 'r' && key != delvar) {
                    params[key] = val;
                }
            }
        }
        var arr = str.split('&');
        for (i in arr) {
            var pos = arr[i].indexOf('=');
            if (pos > 0) {
                key = arr[i].substr(0,pos);
                val = arr[i].substr(pos+1);
                if (key != 'r') {
                    params[key] = val;
                }
            }
        }
        hash = new Array();
        for (key in params) {
            hash.push(key+'='+params[key]);
        }
        location.hash = hash.join('&');

        //location.hash += '&' + str;
        if (process || process==undefined) {
            processLocationHash();
        }
    }    
}

function delFromLocationHash(variable)
{
    var params = new Array();
    var arr = location.hash.substr(1).split('&');
    for (i in arr) {
        var pos = arr[i].indexOf('=');
        if (pos > 0) {
            key = arr[i].substr(0,pos);
            val = arr[i].substr(pos+1);
            if (key != 'r' && key!= variable) {
                params.push = arr[i];
            }
        }
    }
    location.hash = params.join('&');
}

function setLocationHash(str, process)
{
    if (str != '') {
        location.hash = str;
    } else {
        location.hash = '.';
    }
    if (process || process==undefined) {
        processLocationHash();
    }
}

var lastHash = '';

function processLocationHash()
{
    if (location.hash == lastHash) { return true; }
    if (location.hash == '#.' || location.hash == '' || location.hash == '#') {
        lastHash = location.hash;
        $('.ajaxPager .first a').each(function() {
            $(this).click();
        });
    } else if (location.hash != '' && location.hash != '#') {
        var arr = location.hash.substr(1).split('&');
        var params = new Array();
        for (i in arr) {
            var pos = arr[i].indexOf('=');
            if (pos > 0) {
                key = arr[i].substr(0,pos);
                val = arr[i].substr(pos+1);
                if (key != 'r') {
                    var ppos = key.indexOf('_');
                    if (ppos > 0) {
                        unit = key.substr(0,ppos);
                        content_id = unit.match(/[0-9]*/gi).join('');
                        rel = unit.substr(0, unit.indexOf(content_id));
                        pageunit = $('.pageunit[rel="'+rel+'"][content_id='+content_id+']');
                        if (pageunit.length) {
                            $('#pageunitpanel').appendTo('body');
                            pageunit_id = pageunit.attr('id').replace('cms-pageunit-','');
                            updatePageunit(pageunit_id, '.pageunit[rev='+pageunit.attr('rev')+']', null, arr.join('&'));
                        }
                    }
                }
            }
        }        
    }
    lastHash = location.hash;
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

            var height = $(window).height()-25;
            var width = $(window).width()-50;
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
            dlg.parents('.ui-dialog').css('position', 'fixed');
        }
    });

}