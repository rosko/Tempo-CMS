<?php
$this->pageTitle = $model->title;
$this->keywords = $model->keywords;
$this->description = $model->description;

if (!Yii::app()->user->isGuest) {

    if (!$model->active) {
        $this->pageTitle = '[Страница отключена] ' . $this->pageTitle;
    }

    $cs=Yii::app()->getClientScript();
    $cs->registerCoreScript('jquery');
    $cs->registerCoreScript('yiiactiveform');
    $cs->registerCoreScript('jquery.ui');
    $cs->registerCssFile(Yii::app()->params->jui['themeUrl'] . '/'. Yii::app()->params->jui['theme'].'/jquery-ui.css');

    $cs->registerScriptFile('/js/jquery.scrollTo.js');
	$cs->registerScriptFile('/js/jquery.cookie.js');
    $cs->registerScriptFile('/js/jquery.hotkeys.js');

    $cs->registerScriptFile('/3rdparty/fancybox/jquery.fancybox-1.3.1.js');
    $cs->registerCssFile('/3rdparty/fancybox/jquery.fancybox-1.3.1.css');

    $cs->registerScriptFile('/js/lib.js');

        $dir = Yii::getPathOfAlias('ext.jsTree.source');
        $baseUrl = Yii::app()->getAssetManager()->publish($dir);
        $cs->registerScriptFile($baseUrl.'/jquery.jstree.js');
		
    $cs->registerScript('cms-area', <<<EOD

        $('body').attr('rel', {$model->id});
        $.data(document.body, 'title', '{$model->title}');

        // Настройки и обработчики перещения юнитов на странице
        $('.cms-area').sortable({
            connectWith: '.cms-area',
            placeholder: 'cms-pageunit-highlight',
            revert: true,
            opacity:0.8,
            forcePlaceholderSize:true,
            cancel:'.cms-pageunit-menu,.cms-empty-area-buttons',
            update:function(event, ui) {
                var pageunit_id = $(ui.item).attr('id').replace('cms-pageunit-','');
                var area_name = getAreaNameByPageunit(ui.item);
                if (ui.sender) {
                    // Запрос на обновление предыдущей области + перемещенному элементу указывается новое значение в area
//                    var old_area = $(ui.sender).attr('id').replace('cms-area-', '');
//                    ajaxSaveArea($(ui.sender), area_name, {$model->id}, 'pageunit_id='+pageunit_id+'&old_area='+old_area);

                } else {
                    // Запрос на обновление текущей области
                    ajaxSaveArea(getAreaByPageunit(ui.item), area_name, {$model->id}, 'pageunit_id='+pageunit_id);
                }
            },
            start:function(event, ui) {
                hidePageunitPanel(ui.item);
                pageunit_dragging = true;
                $('.cms-area').addClass('potential');
                $('.cms-area').each(function() {
                    if ($(this).find('.cms-pageunit').length == 0)
                        $(this).addClass('cms-empty-area');
                });
                CmsAreaEmptyCheck();
            },
            stop:function(event, ui) {
                pageunit_dragging = false;
                showPageunitPanel(ui.item);
                $('.cms-area').removeClass('potential').removeClass('cms-empty-area');
                CmsAreaEmptyCheck();
            }
        }).disableSelection();
        
        $('.cms-pageunit').css('cursor', 'move');
        
        CmsPageunitDisabling();
        CmsPageunitHovering();
        CmsPageunitEditing();

        $('#cms-pageunit-menu').dblclick(function() {
            return false;
        });
        
        // Обработчик нажатия кнопки "Добавить юнит"
        $('.cms-btn-pageunit-add').live('click', function() {
            var pageunit = $(this).parents('.cms-pageunit');
            if (pageunit.length) {
                pageunit  = pageunit.eq(0);
                hidePageunitPanel(pageunit);
                var pageunit_id = pageunit.attr('id').replace('cms-pageunit-','');
            } else {
                var pageunit_id = '0';
            }
            var area_name = $(this).parents('.cms-area').eq(0).attr('id').replace('cms-area-','');
            $('#cms-pageunit-add').find('.cms-btn-pageunit-create').attr('rel', area_name);
            $('#cms-pageunit-add').find('.cms-btn-pageunit-create').attr('rev', pageunit_id);
            showSplash($('#cms-pageunit-add'));
            return false;
        });
        
        // Обработчик для выбора типа юнита при создании
        $('.cms-btn-pageunit-create').click(function() {
            var type = $(this).attr('id').replace('cms-button-create-', '');
            var area_name = $(this).attr('rel');
            var pageunit_id = $(this).attr('rev');
            var page_id = {$model->id};
            var url = '/?r=page/unitAdd&page_id='+page_id+'&pageunit_id='+pageunit_id+'&area='+area_name+'&type='+type;
            ajaxSave(url, '', 'GET', function(id) {
                GetOutPageunitPanel();
                hideSplash();
                id = jQuery.parseJSON(id);
                if (pageunit_id != '0') {
                    var pageunit = $('#cms-pageunit-'+pageunit_id);
                } else {
                    var pageunit = $('#cms-area-'+area_name).find('.cms-empty-area-buttons').eq(0);
                }
                pageunit.after('<div id="cms-pageunit-'+id.pageunit_id+'" class="cms-pageunit cms-unit-'+type+'" rel="'+type+'" rev="'+id.unit_id+'"></div>');
                
                var orig_bg = $('#cms-pageunit-'+id.pageunit_id).css('backgroundColor');
                $('#cms-pageunit-'+id.pageunit_id).load('/?r=page/unitView&pageunit_id='+id.pageunit_id+'&id='+page_id, function() {
                    CmsPageunitHovering();
                    CmsPageunitDisabling();
                    CmsPageunitEditing();
                }).css('backgroundColor', '#FFFF00');
                $('#cms-pageunit-'+id.pageunit_id).animate({
                    backgroundColor: orig_bg
                }, 2500);
                $.scrollTo('#cms-pageunit-'+id.pageunit_id, 'normal', {
                    offset: -10
                });
            });
            return false;
        });

        
        // Обработчик нажатия кнопки "Удалить юнит"
        $('.cms-btn-pageunit-delete').click(function() {
            var pageunit = $(this).parents('.cms-pageunit').eq(0);
            hidePageunitPanel(pageunit);
            pageunit.addClass('selected');
            pageunitDeleteDialog(pageunit.attr('rev'), pageunit.attr('id').replace('cms-pageunit-',''), {$model->id});
            return false;
        });

        // Обработчик нажатия кнопки "Переместить юнит выше"
        $('.cms-btn-pageunit-moveup').click(function() {
            var pageunit = $(this).parents('.cms-pageunit').eq(0);
            if (pageunit.prev().length) {
                pageunit.insertBefore(pageunit.prev());
                showPageunitPanel(pageunit);
                area = getAreaByPageunit(pageunit);
                ajaxSaveArea(area, getAreaName(area), {$model->id}, 'pageunit_id='+pageunit.attr('id'));
            }
            return false;
        });

        // Обработчик нажатия кнопки "Переместить юнит ниже"
        $('.cms-btn-pageunit-movedown').click(function() {
            var pageunit = $(this).parents('.cms-pageunit').eq(0);
            if (pageunit.next().length) {
                pageunit.insertAfter(pageunit.next());
                showPageunitPanel(pageunit);
                area = getAreaByPageunit(pageunit);
                ajaxSaveArea(area, getAreaName(area), {$model->id}, 'pageunit_id='+pageunit.attr('id'));
            }
            return false;
        });

        // Обработчик нажатия кнопки "Размещение блока на других страницах"
        $('.cms-btn-pageunit-set').click(function() {
            var pageunit = $(this).parents('.cms-pageunit').eq(0);
            pageunit.addClass('selected');
            var pageunit_id = pageunit.attr('id').replace('cms-pageunit-','');
            var unit_id = pageunit.attr('rev');
            pageunitSetDialog({$model->id}, pageunit_id, unit_id);
            return false;
        });

        // Обработчик нажатия кнопки "Редактировать свойства юнита"
        $('.cms-btn-pageunit-edit').click(function() {
            var pageunit = $(this).parents('.cms-pageunit').eq(0);
            pageunitEditForm(pageunit);
            return false;
        });


        // Обработчик нажатия кнопки "Редактировать свойства страницы"
        $('.cms-btn-page-edit').click(function(){
            pageEditForm();
            return false;
        });
        
        // Обработчик нажатия кнопки "Добавить страницу"
        $('.cms-btn-page-add').click(function(){
            pageAddForm();
            return false;
        });

        // Обработчик нажатия кнопки "Файлменеджер"
        $('.cms-btn-filemanager').click(function(){
            var url = '/3rdparty/fckeditor/editor/plugins/imglib/index.html';
            window.open( url, 'imglib','width=800, height=600, location=0, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes');
            return false;
        });
        
        // Обработчик нажатия кнопки "Редактировать настроки сайта"
        $('.cms-btn-settings').click(function(){
            hidePageunitPanel();
            $.ajax({
                url: '/?r=page/siteSettings',
                method: 'GET',
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
                        resizable: true
                    });
                }
            });
            return false;
        });

        // Обработчик нажатия кнопки "Карта сайта"
        $('.cms-btn-sitemap').click(function(){
            hidePageunitPanel();
            var page_id = {$model->id};
            $.ajax({
                url: '/?r=page/siteMap&id='+page_id,
                method: 'GET',
                cache: false,
                beforeSend: function() {
                    showInfoPanel(cms_html_loading_image, 0);                    
                },
                success: function(html) {
                    hideInfoPanel();
                    $('#cms-page-edit').html(html);
                    showSplash($('#cms-page-edit'), {
                        resizable: true,
                        draggable: true
                    });
                }
            });
            return false;
        });
        
        // Обработчик нажатия кнопки "Выход из режима редактирования"
        $('.cms-btn-exit').click(function(){
            if (confirm('Действительно выйти из режима управления сайтом?')) {
                location.href = '{$this->createUrl('site/logout')}';
            }
            return false;
        });
        
        // Отображение тулбара
        var body_width = $('body').width();
        var body_height = $('body').height();
        var toolbar_width = $('#cms-toolbar').width();
        var cms_toolbar_top = $.cookie('cms_toolbar_top') ? $.cookie('cms_toolbar_top') : '20';
        var cms_toolbar_left = $.cookie('cms_toolbar_left') ? $.cookie('cms_toolbar_left') : body_width-toolbar_width-40;
        if (cms_toolbar_top > body_height-30) { cms_toolbar_top = body_height - 30; }
        if (cms_toolbar_left > body_width-30) { cms_toolbar_left = body_width - 30 }
        $('#cms-toolbar').appendTo('body').show().draggable({
            stop: function(event, ui) {
                $.cookie('cms_toolbar_top', ui.offset.top, { expires: 30, path: '/'});
                $.cookie('cms_toolbar_left', ui.offset.left, { expires: 30, path: '/'});
            }
        }).css({
            position:'fixed',
            left:cms_toolbar_left+'px',
            top:cms_toolbar_top+'px',
            width:'92px',
            'z-index': 1000
        });

        // Отображение диалога "Заполнить страницу" на пустой странице
        if ($('.pageunit').length == 0)
        {
            var page_id = $('body').attr('rel');
            hidePageunitPanel();
            $.ajax({
                url: '/?r=page/pageFill&id='+page_id,
                method: 'GET',
                cache: false,
                beforeSend: function() {
                    showInfoPanel(cms_html_loading_image, 0);                    
                },
                success: function(html) {
                    hideInfoPanel();
                    $('#cms-page-fill').html(html);
                    AjaxifyForm('#cms-page-fill', $('#cms-page-fill').find('form').eq(0), function(f) {
                    }, function (html) {
                    });
                    showSplash($('#cms-page-fill'));
                }
            });
        }
        


EOD
, CClientScript::POS_READY);


?>

    <div id="cms-toolbar" class="cms-panel cms-toolbar cms-splash">
        <ul>
            <li><a class="cms-button-medium cms-button-medium-edit cms-btn-page-edit" title="Свойства страницы" href="#"></a></li>
            <li><a class="cms-button-medium cms-button-medium-add cms-btn-page-add" title="Создать новую страницу" href="#"></a></li>
            <li><a class="cms-button-medium cms-button-medium-files cms-btn-filemanager" title="Хранилище файлов" href="#"></a></li>
        </ul>
        <ul>
            <li><a class="cms-button-medium cms-button-medium-settings cms-btn-settings" title="Настройки всего сайта" href="#"></a></li>
            <li><a class="cms-button-medium cms-button-medium-sitemap cms-btn-sitemap" title="Карта сайта" href="#"></a></li>
            <li><a class="cms-button-medium cms-button-medium-exit  cms-btn-exit" title="Выход" href="#"></a></li>
        </ul>
    </div>


<div class="hidden">
    <div id="cms-pageunit-menu" class="cms-panel cms-pageunit-menu">
        <ul>
            <li><a class="cms-button-big cms-button-add cms-btn-pageunit-add" title="Добавить еще один блок" href="#"></a></li>
            <li><a class="cms-button-big cms-button-edit cms-btn-pageunit-edit" title="Редактировать" href="#"></a></li>
            <li><a class="cms-button-big cms-button-move cms-btn-pageunit-set" title="Размещение блока на других страницах" href="#"></a></li>
            <li><a class="cms-button-big cms-button-up cms-btn-pageunit-moveup" title="Переместить выше" href="#"></a></li>
            <li><a class="cms-button-big cms-button-down cms-btn-pageunit-movedown" title="Переместить ниже" href="#"></a></li>
            <li><a class="cms-button-big cms-button-delete cms-btn-pageunit-delete" title="Удалить этот блок" href="#"></a></li>
        </ul>
    </div>


    <div id="cms-pageunit-add" class="cms-splash">
        <h3>Добавить блок</h3>
        <ul>
        <?php
            $unit_types = Unit::getTypes();
            $unit_types_count = count($unit_types);
            $i=0;
            foreach ($unit_types as $unit_class) {
                $i++;
                $unit_type = substr(strtolower($unit_class),4);
                ?><li><a class="cms-button cms-btn-pageunit-create" id="cms-button-create-<?=$unit_type?>" title="<?=$unit_class::NAME?>" href="#" ><img src="<?=$unit_class::ICON?>" alt="<?=$unit_class::NAME?>" /> <?=$unit_class::NAME?></a></li><?php
                if ($i == ceil($unit_types_count/2)) {
                    ?></ul><ul><?php
                }
            }
        ?>
        </ul>
    </div>
    
    <div id="cms-pageunit-edit">
    </div>

    <div id="cms-pageunit-delete" class="cms-splash">
    </div>

    <div id="cms-pageunit-set" class="cms-splash">
    </div>

    <div id="cms-page-fill" class="cms-splash">
    </div>

    <div id="cms-page-edit">
    </div>

    <div id="cms-page-delete" class="cms-splash">
    </div>
    
    <div id="cms-dialog">
    </div>
</div>


<div class="top fixed cms-panel" id="cms-info"></div>

<?php
}