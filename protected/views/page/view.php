<?php
$this->pageTitle = $model->title;

$cs=Yii::app()->getClientScript();

$cs->registerScript('all', <<<EOD

        $('<div id="cms-statusbar"></div>').prependTo('body');
        $('<div id="cms-notification"></div>').prependTo('body');
            $('#cms-statusbar').jnotifyInizialize({
                oneAtTime: true
            })
            $('#cms-notification')
                .jnotifyInizialize({
                    oneAtTime: false,
                    appendType: 'append'
                })
                .css({ 'position': 'fixed',
                    'marginTop': '10px',
                    'left': Math.ceil($('body').width()/2-125)+'px',
                    'width': '300px',
                    'z-index': '100000'
                });


EOD
, CClientScript::POS_READY);

$js = '';
$flashes = Yii::app()->user->getFlashes();
foreach ($flashes as $k => $flash)
{
    if (!is_string($flash)) continue;
    $params = explode('-', $k);
    $k = $params[0];
    $params = array_values(array_slice($params, 1));
    $types = array('error', 'hint', 'message');
    $type = 'message';
    foreach ($types as $t) {
        if (in_array($t, $params)) {
            $type = $t;
            break;
        }
    }
    $options = array(
        'type'=> $type,
        'permanent'=> in_array('permanent', $params),
        'showIcon' => !in_array('noicon', $params)
    );
    $options = CJavaScript::encode($options);
    $flash = CJavaScript::encode($flash);
    $js .= "notify({$flash}, {$options});\n";
}
if ($js)
    $cs->registerScript('flashes', $js, CClientScript::POS_READY);


if (!Yii::app()->user->isGuest) {

    if (!$model->active) {
        $this->pageTitle = '['.Yii::t('cms', 'Page unactive').'] ' . $this->pageTitle;
    }

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
            opacity:1,
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
                $(ui.helper).find('.cms-panel').hide();
                $('.cms-area').addClass('potential');
                $('.cms-area').each(function() {
                    if ($(this).find('.cms-pageunit').length == 0)
                        $(this).addClass('cms-empty-area');
                });
                CmsAreaEmptyCheck();
            },
            stop:function(event, ui) {
                $('.cms-area').removeClass('potential').removeClass('cms-empty-area');
                CmsAreaEmptyCheck();
            }
        }).disableSelection();
        
        $('.cms-pageunit').css('cursor', 'move');
        
        CmsAreaEmptyCheck();
        $('.cms-pageunit').live('mouseenter', function() {
            $(this).addClass('hover');
        }).live('mouseleave', function() {
            $(this).removeClass('hover');
        });

        $('.cms-pageunit').live('dblclick', function() {
            clearSelection();
            pageunitEditForm(this);
            return false;
        })

        // Обработчик для выбора типа юнита при создании
        $('.cms-btn-pageunit-create').click(function() {
            var type = $(this).attr('id').replace('cms-button-create-', '');
            var area_name = $(this).attr('rel');
            var pageunit_id = $(this).attr('rev');
            var page_id = {$model->id};
            var url = '/?r=page/unitAdd&page_id='+page_id+'&pageunit_id='+pageunit_id+'&area='+area_name+'&type='+type;
            ajaxSave(url, '', 'GET', function(id) {
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
                    CmsAreaEmptyCheck();
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
        
        // Отображение диалога "Заполнить страницу" на пустой странице
        if ($('.pageunit').length == 0)
        {
            var page_id = $('body').attr('rel');
            $.ajax({
                url: '/?r=page/pageFill&id='+page_id,
                type: 'GET',
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


    if (Yii::app()->settings->getValue('autoSave')) {

        $cs->registerScript('autoSave', <<<EOD
            setInterval(function() {
                $('form input[name=apply]:submit').each(function() {
                    $(this).parents('form').attr('rev', 'apply').trigger('submit');
                });
            }, 30000);
EOD
        , CClientScript::POS_READY);
    
    }

/*
 * Общая панель инструментов для всего сайта
 */
    $txtSureExit = Yii::t('cms', 'Really exit from site edit mode?');
    $this->widget('Toolbar', array(
        'id' => 'toolbar',
        'location' => array(
            'selector' => 'body',
            'position' => array('absolute', 'top', 'right'),
            'show' => 'always',
            'draggable' => true,
            //'resizable' => true,
            'save' => true,
        ),
        'vertical'=>true,
        'rows'=>2,
        'buttons'=>array(
            'edit' => array(
                'icon' => 'edit',
                'title' => Yii::t('cms', 'Page properties'),
                'click' => 'js:function(){ pageEditForm(); return false; }',
            ),
            'settings' => array(
                'icon' => 'settings',
                'title' => Yii::t('cms', 'Site properties'),
                'click' => <<<EOD
js:function(){
            $.ajax({
                url: '/?r=page/siteSettings',
                type: 'GET',
                cache: false,
                beforeSend: function() {
                    showInfoPanel(cms_html_loading_image, 0);
                },
                success: function(html) {
                    hideInfoPanel();
                    $('#cms-page-edit')
                        .html(html)
                        .width(Math.ceil($(window).width()*0.9))
                        .height(Math.ceil($(window).height()*0.85));
                    $('#cms-page-edit').find('form').find('input[type="submit"]').click(function() {
                        $(this).parents('form').attr('rev', $(this).attr('name'));
                    });
                    showSplash($('#cms-page-edit'), {
                        draggable: true,
                        resizable: true,
                        centerOnScroll: true
                    });
                }
            });
            return false;
        }
EOD
            ),
            'pageadd' => array(
                'icon' => 'add',
                'title' => Yii::t('cms', 'Create new page'),
                'click' => 'js:function() { pageAddForm(); return false; }',
            ),
            'sitemap' => array(
                'icon' => 'sitemap',
                'title' => Yii::t('cms', 'Sitemap'),
                'click' => <<<EOD
js:function(){
            var page_id = {$model->id};
            $.ajax({
                url: '/?r=page/siteMap&id='+page_id,
                type: 'GET',
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
        }
EOD
            ),
            'filemanager' => array(
                'icon' => 'files',
                'title' => Yii::t('cms', 'File manager'),
                'click' => <<<EOD
js:function(){
            var url = '/3rdparty/fckeditor/editor/plugins/imglib/index.html';
            window.open( url, 'imglib','width=800, height=600, location=0, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes');
            return false;
}
EOD
            ),
            'exit' => array(
                'icon' => 'exit',
                'title' => Yii::t('cms', 'Exit'),
                'click' => <<<EOD
js:function(){
            if (confirm('{$txtSureExit}')) {
                location.href = '{$this->createUrl('site/logout')}';
            }
            return false;
        }
EOD
            ),
        ),
    ));

/*
 * Панель инструментов для блоков
 */
    $this->widget('Toolbar', array(
        'id' => 'pageunitpanel',
        'location' => array(
            'selector' => '.cms-pageunit',
            'position' => array('outter', 'left', 'top'),
            'show' => 'hover',
            'draggable' => false,
            //'resizable' => true,
            //'save' => true,
        ),
        'zIndex' => 1010,
        'iconSize' => '32x32',
        'vertical'=>true,
        'rows'=>1,
        'dblclick' => 'js:function() { return false; }',
        'buttons'=>array(
            'add' => array(
                'icon' => 'add',
                'title' => Yii::t('cms', 'Add another unit'),
                'click' => <<<EOD
js:function() {
            pageunitAddForm(this);
            return false;
        }
EOD
            ),
            'edit' => array(
                'icon' => 'edit',
                'title' => Yii::t('cms', 'Edit'),
                'click' => <<<EOD
js:function() {
            var pageunit = $(this).parents('.cms-pageunit').eq(0);
            pageunitEditForm(pageunit);
            return false;
        }
EOD
            ),
            'move' => array(
                'icon' => 'move',
                'title' => Yii::t('cms', 'Unit location on pages'),
                'click' => <<<EOD
js:function() {
            var pageunit = $(this).parents('.cms-pageunit').eq(0);
            fadeIn(pageunit, 'selected');
            var pageunit_id = pageunit.attr('id').replace('cms-pageunit-','');
            var unit_id = pageunit.attr('rev');
            pageunitSetDialog({$model->id}, pageunit_id, unit_id);
            return false;
        }
EOD
            ),
            'up' => array(
                'icon' => 'up',
                'title' => Yii::t('cms', 'Move up'),
                'click' => <<<EOD
js:function() {
            var pageunit = $(this).parents('.cms-pageunit').eq(0);
            if (pageunit.prev().length) {
                pageunit.insertBefore(pageunit.prev());
                area = getAreaByPageunit(pageunit);
                var pageunit_id = pageunit.attr('id').replace('cms-pageunit-','');
                ajaxSaveArea(area, getAreaName(area), {$model->id}, 'pageunit_id='+pageunit_id);
            }
            return false;
        }
EOD
            ),
            'down' => array(
                'icon' => 'down',
                'title' => Yii::t('cms', 'Move down'),
                'click' => <<<EOD
js:function() {
            var pageunit = $(this).parents('.cms-pageunit').eq(0);
            if (pageunit.next().length) {
                pageunit.insertAfter(pageunit.next());
                area = getAreaByPageunit(pageunit);
                var pageunit_id = pageunit.attr('id').replace('cms-pageunit-','');
                ajaxSaveArea(area, getAreaName(area), {$model->id}, 'pageunit_id='+pageunit_id);
            }
            return false;
        }
EOD
            ),
            'delete' => array(
                'icon' => 'delete',
                'title' => Yii::t('cms', 'Delete the unit'),
                'click' => <<<EOD
js:function() {
            var pageunit = $(this).parents('.cms-pageunit').eq(0);
            fadeIn(pageunit, 'selected');
            pageunitDeleteDialog(pageunit.attr('rev'), pageunit.attr('id').replace('cms-pageunit-',''), {$model->id});
            return false;
        }
EOD
            ),
        )
    ));


?>

<div class="hidden">

    <div id="cms-pageunit-add" class="cms-splash">
        <h3><?=Yii::t('cms', 'Add unit')?></h3>
        <ul>
        <?php
            $unit_types = Unit::getTypes();
            $unit_types_count = count($unit_types);
            $i=0;
            foreach ($unit_types as $unit_class) {
                $i++;
                $unit_type = Unit::getUnitTypeByClassName($unit_class);
                ?><li><a class="cms-button cms-btn-pageunit-create" id="cms-button-create-<?=$unit_type?>" title="<?=$unit_class::name()?>" href="#" ><img src="<?=$unit_class::ICON?>" alt="<?=$unit_class::name()?>" /> <?=$unit_class::name()?></a></li><?php
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

<?php
}