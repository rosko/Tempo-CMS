// Диалоговое окно

function openDialog(content, options) {
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
                form.attr('rev', button.attr('name')).submit();
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
        },
        onClose: function() {
            fadeOut('.selected', 'selected');
            $(content).html('');
            if ($.isFunction(options.onClose)) {
                options.onClose(this);
            }
        }
    });
}

function closeDialog()
{
    $.topbox.close();
}

function loadDialog(url, opts)
{
    if (opts == null) {
        opts = {};
    }
    $.ajax({
        url:url,
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
            if (opts.pageunit_id != undefined)
                dlg.find('form').attr('rel', pageunit_id);
            dlg.find('form').find('input[type="submit"]').click(function() {
                $(this).parents('form').attr('rev', $(this).attr('name'));
            });
            dlg.find('form').submit(function(){
                if ($.isFunction(opts.onSubmit)) {
                    opts.onSubmit(this);
                }
                if (opts.ajaxify) {
                    ajaxSubmitForm($(this), null, null, {
                        onSuccess: function(html) {
                            if ($.isFunction(opts.onSave)) {
                                opts.onSave(html);
                            }
                        }
                    });
                }
                return false;
            });

            openDialog(dlg, opts);
        }
    });

}

