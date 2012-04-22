// Сколько секунд отображается всплывающая инфо-панель
var cmsInfoPanelTimeout = 3;
var cmsInfoPanelTimer = null;

// =============================================================
// Функции для отображение разных уведомлений и диалоговых окон

// Уведомляющая надпись
//
// @param string html html-код надписи
// @param int timeout время отображения надписи
// @param boolean error является ли эта надпись сообщением об ошибке

function cmsShowInfoPanel(html, timeout, error)
{
    clearTimeout(cmsInfoPanelTimer);
    var t = 'message';
    if (error) {
        t = 'error';
    }
    if (timeout == null) {
        timeout = cmsInfoPanelTimeout;
    }
    cmsNotify(html, {
        type: t,
        disappearTime: timeout
    });
    if (timeout > 0) {
        cmsInfoPanelTimer = setTimeout('cmsHideInfoPanel()', timeout*1000);
    }
}

function cmsHideInfoPanel()
{
    cmsRemoveLastNotification();
}

function cmsNotify(message, opts)
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

function cmsRemoveLastNotification()
{
    var obj = $('#cms-notification .jnotify-item:last');
    obj.animate({ opacity: '0' }, 600, function() {
        obj.parent().animate({ height: '0px' }, 300,
              function() {
                  obj.parent().remove();
                  if (navigator.userAgent.match(/MSIE (\d+\.\d+);/)) {
                      obj.parent().parent().removeClass('IEsucks');
                  }
        });
    });
}

function cmsGetLastNotification()
{
    return $('#cms-notification .jnotify-item:last').text();
}

function cmsReloadPageWidget(pageWidgetId, selector, onSuccess, data)
{
    var pageId = $('body').attr('rel');
    var language = $('body').data('language');
    var locationString = cmsGetLocationSearch();
    if (data == undefined) { data = ''} else {
        if (locationString)
            data = '&'+data;
    }
    $.ajax({
        url: '/?r=view/widget&pageWidgetId='+pageWidgetId+'&pageId='+pageId+'&language='+language,
        data: locationString+data,
        async: false,
        cache: false,
        success: function(html) {
            $(selector).html(html);
            if ($.isFunction(cmsAreaEmptyCheck)) {
                cmsAreaEmptyCheck();
            }
            if ($.isFunction(onSuccess)) {
                onSuccess(html);
            }
        }
    });
}

function cmsGetLocationSearch()
{
    var arr = window.location.search.substring(1).split('&');
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

function cmsAddToLocationHash(str, process, delvar)
{
    if (location.hash == '' || location.hash == '#' || location.hash == '#.') {
        cmsSetLocationHash(str);
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
            cmsProcessLocationHash();
        }
    }    
}

function cmsDelFromLocationHash(variable)
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

function cmsSetLocationHash(str, process)
{
    if (str != '') {
        location.hash = str;
    } else {
        location.hash = '.';
    }
    if (process || process==undefined) {
        cmsProcessLocationHash();
    }
}

var lastHash = '';

function cmsProcessLocationHash()
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
                        widget = key.substr(0,ppos);
                        contentId = widget.match(/[0-9]*/gi).join('');
                        rel = widget.substr(0, widget.indexOf(contentId));
                        pageWidget = $('.pagewidget[rel="'+rel+'"][content_id='+contentId+']');
                        if (pageWidget.length) {
                            $('#pagewidgetpanel').appendTo('body');
                            pageWidgetId = pageWidget.attr('id').replace('cms-pagewidget-','');
                            cmsReloadPageWidget(pageWidgetId, '.pagewidget[rev='+pageWidget.attr('rev')+']', null, arr.join('&'));
                        }
                    }
                }
            }
        }        
    }
    lastHash = location.hash;
}

// =============================================================


function cmsAjaxifyForm(container, f, onSubmit, onSave, onClose, validate)
{
    f.attr('target', container);
    f.submit(function(){
        cmsAjaxSave(f.attr('action'), f.serialize(), f.attr('method'), function(html) {
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
                        cmsCloseDialog();
                } else {
                    if (!validate) {
                        $(container).html(html);
                    }
                    $(container).find('form').eq(0).attr('rel', rel);
                    cmsAjaxifyForm(container, $(container).find('form').eq(0), onSubmit, onSave, onClose, validate);
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
        return confirm(cmsI18n.cms.deleteWarning);
    });
    f.find('input').bind('keydown', 'ctrl+return', function() {
        $(this).after('<input class="submit" type="hidden" name="save" value="save" />');
        $(this).parents('form').submit();
    });

}

// =============================================================

function cmsStrReplace ( search, replace, subject ) {

    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Gabriel Paderni
    if(!(replace instanceof Array)){
		replace=new Array(replace);
		if(search instanceof Array){
			while(search.length>replace.length){
				replace[replace.length]=replace[0];
			}
		}
	}

	if(!(search instanceof Array))search=new Array(search);
	while(search.length>replace.length){
		replace[replace.length]='';
	}

	if(subject instanceof Array){
		for(k in subject){
			subject[k]=cmsStrReplace(search,replace,subject[k]);
		}
		return subject;
	}

	for(var k=0; k<search.length; k++){
		var i = subject.indexOf(search[k]);
		while(i>-1){
			subject = subject.replace(search[k], replace[k]);
			i = subject.indexOf(search[k],i);
		}
	}

	return subject;

}

function cmsStrToLower(str) {
    return str.toLowerCase();
}

function cmsReadableFileSize(size) {
    var units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    var i = 0;
    while(size >= 1024) {
        size /= 1024;
        ++i;
    }
    return size.toFixed(1) + ' ' + units[i];
}

function cmsFileBaseName(path) {
    var parts = path.split( '/' );
    return parts[parts.length-1];
}

function cmsFileExtension(filename) {
    return filename.split('.').pop();
}

function cmsFileSize(filename, handler) {
    $.ajax({
        type: "HEAD",
        url: filename,
        complete: function (jqXHR) {
            if ($.isFunction(handler)) {
                handler(jqXHR.getResponseHeader("Content-length"));
            }
        }
    });
}