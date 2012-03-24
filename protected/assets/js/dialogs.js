
// =============================================================
// Открытие диаологового окна
// =============================================================
//
// @param string content содержимое диалогового окна
// @param object options дополнительные настройки
// boolean simpleClose позволять закрывать кнопкой Escape
// array buttons список кнопок, index - название кнопки, value - фукнция выполняемая при нажатии
// string title заголовок диалогового окна
// int width ширина
// int height высота
// function onOpen функция выполняемая при открытии диалога (первым параметром в функцию передается объект topbox)
// function onClose функция выполняемая при закрытии диалога (первым параметром в функцию передается объект topbox)

function cmsOpenDialog(content, options) {
    if (options == null) {
        options = {};
    }
    if (options.simpleClose == undefined) {
        options.simpleClose = true;
    }
    if (options.buttons == undefined) {
        options.buttons = {};
        $(content).find('input[type=submit]').each(function() {
            b = $(this);
            options.buttons[b.val()] = function() {
                form = $(this).parents('.topbox-window:eq(0)').find('form');
                button = form.find('input[value="'+$(this).html()+'"]');
                form.attr('rev', button.attr('name'));
                form.submit();
            }
            b.hide();
        });
    }

    $.topbox.show(content, {
        title: options.title,
        closeOnEsc: options.simpleClose,
        theme: 'classic',
        width: options.width,
        height: options.height,
        speed: 500,
        easing: 'swing',
        buttons: options.buttons,
        beforeShow: function() {
            if (options.buttons) {
                $(content).find('.row.buttons').hide();
            }
            content.css({'visibility':'visible'});
            box = this.instances[this.instances.length-1];
            this.setTitle(box.find('.cms-caption').html());
            box.find('.cms-caption').remove();
        },
        onShow: function() {
            if ($.isFunction(options.onOpen)) {
                options.onOpen(this);
            }
/*            var w = $(this).width();
            var h = $(this).height();
            var b = $(this).find('.cms-dialog');
            b.addClass('nano')
            b.wrapInner('<div class="content"></div>');
            b.height(h-112).css('left','0px');
            $(this).find('.cms-dialog').nanoScroller();*/
            $('html').css('overflow', 'hidden');
        },
        onClose: function() {
            cmsFadeOut('.selected', 'selected');
            $(content).html('');
            if ($.isFunction(options.onClose)) {
                options.onClose(this);
            }
            $('html').css('overflow', 'auto');
        }
    });
}

// =============================================================
// Закрытие диалогового окна
// =============================================================

function cmsCloseDialog()
{
    $.topbox.close();
}

// =============================================================
// Загрузка диалогового окна с помощью ajax-запроса
// =============================================================
//
// @param string url адрес запроса
// @param object opts дополнительные параметры
// function onLoad функция выполняемая при загрузке контента через ajax (первым параметром передается полученный из запроса html-код)
// string className имя css-класса для диалога
// string id id диалога
// string pageWidgetId id страничного блока к которому относится этот диалог
// function onSubmit функция выполняемая при отправке формы через диалог (первым параметром передается объект формы)
// boolean ajaxify осуществлять ли отправку формы диалога через ajax
// function onSave функция выполняемая при успешной отправке формы диалога (первым параметром передается html-код ответа)
// а также все, что относятся к параметру options в функции cmsOpenDialog

function cmsLoadDialog(url, opts)
{
    if (opts == null) {
        opts = {};
    }
    $.ajax({
        url:url,
        cache: false,
        opts: opts,
        beforeSend: function() {
            cmsShowInfoPanel(cmsHtmlLoadingImage, 0);
        },
        success: function(html) {
            cmsHideInfoPanel();
            if ($.isFunction(opts.onLoad)) {
                opts.onLoad(html);
            }
            var dlg = $('#cms-dialog').clone().css({'visibility':'hidden'});
            if (opts.className != undefined)
                dlg.addClass(opts.className)
            dlg.addClass('cms-dialog')
            dlg.appendTo('body');
            dlg.html(html);
            if (opts.id == undefined) {
                opts.id = '#cms-dialog-'+dlg.find('form').attr('id');
            }
            dlg.attr('id', opts.id)
            if (opts.pageWidgetId != undefined)
                dlg.find('form').attr('rel', opts.pageWidgetId);
            dlg.find('form').find('input[type="submit"]').click(function() {
                $(this).parents('form').attr('rev', $(this).attr('name'));
            });
            dlg.find('form').submit(function(){
                if ($.isFunction(opts.onSubmit)) {
                    opts.onSubmit(this);
                }
                if (opts.ajaxify) {
                    cmsAjaxSubmitForm($(this), null, null, {
                        onSuccess: function(html) {
                            if ($.isFunction(opts.onSave)) {
                                opts.onSave(html);
                            }
                        }
                    });
                }
                return false;
            });
            cmsOpenDialog(dlg, opts);
        }
    });

}

