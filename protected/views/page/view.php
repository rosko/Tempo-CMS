<?php
echo $unitContent;
$this->pageTitle = $model->title;
$language = Yii::app()->language;

$cs=Yii::app()->getClientScript();
$am=Yii::app()->getAssetManager();

$csrfTokenName = Yii::app()->getRequest()->csrfTokenName;
$csrfToken = Yii::app()->getRequest()->getCsrfToken();

$cs->registerScript('all', <<<EOD

        $('body').attr('rel', {$model->id});
        $.data(document.body, 'title', '{$model->title}');
        $.data(document.body, 'language', '{$language}');
        $.data(document.body, 'csrfTokenName', '{$csrfTokenName}');
        $.data(document.body, 'csrfToken', '{$csrfToken}');

        window.setInterval(function() { processLocationHash(); }, 100);

        $('<div id="cms-statusbar"></div>').prependTo('body');
        $('<div id="cms-notification"></div>').prependTo('body');
            $('#cms-statusbar').jnotifyInizialize({
                oneAtTime: true
            });
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

$unitConfig = Unit::loadConfig();
if (Yii::app()->user->hasState('askfill') && isset($unitConfig['UnitRegister']))  {

    $registerUnit = UnitRegister::model()->find('unit_id > 0');
    if ($registerUnit) {
        $shortMessage = '<a href=\''.$registerUnit->getUnitUrl().'\'>'.Yii::t('cms', 'Please fill all required fields in your personal profile. And if necessary, change your password.').'</a>';
        $message = '<h3>'.Yii::t('cms', 'Attention') .'</h3><p>'.$shortMessage.'</p>';
        if (Yii::app()->user->getState('askfill') == 'first') {

            $fancyPath = $am->publish(Yii::getPathOfAlias('application.vendors.fancybox'));
            $cs->registerScriptFile($fancyPath.'/jquery.fancybox-1.3.1.pack.js');
            $cs->registerCssFile($fancyPath.'/jquery.fancybox-1.3.1.css');
            $js .= '$.fancybox("'.$message.'");';
            Yii::app()->user->setState('askfill', 'second');
        } else {
            $flashes['askfill-permanent'] = $shortMessage;
        }
    }
}

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

if (Yii::app()->settings->getValue('ajaxPagerScroll')) {
    $addjs = "$('body').scrollTo(pageunit, 800);";
} else {
    $addjs = '';
}

$js = <<<EOD
$('.ajaxPager a').live('click', function() {
    var pageunit = $(this).parents('.pageunit').eq(0);
    if (pageunit.length) {
        var pos = $(this).attr('href').indexOf('?');
        var data = '';
        if (pos > -1) {
            data = $(this).attr('href').substr(pos+1);
        }
        var pageunit_id = pageunit.attr('id').replace('cms-pageunit-','');
        addToLocationHash(data, false, pageunit.attr('rel')+pageunit.attr('content_id')+'_page');
        $('#pageunitpanel').appendTo('body');
        updatePageunit(pageunit_id, '.pageunit[rev='+pageunit.attr('rev')+']', function() {
            {$addjs}
        }, '&'+data);
    }
    return false;
});
EOD;
$cs->registerScript('ajaxPager', $js, CClientScript::POS_READY);

if (Yii::app()->user->checkAccess('createPage', array('page'=>$model)) ||
    Yii::app()->user->checkAccess('updatePage', array('page'=>$model)) ||
    Yii::app()->user->checkAccess('deletePage', array('page'=>$model))) {

    if (!$model->active) {
        $this->pageTitle = '['.Yii::t('cms', 'Page unactive').'] ' . $this->pageTitle;
    }

    $dir = Yii::getPathOfAlias('ext.jsTree.source');
    $baseUrl = Yii::app()->getAssetManager()->publish($dir);
    $cs->registerScriptFile($baseUrl.'/jquery.jstree.js');

    if (Yii::app()->user->checkAccess('updatePage', array('page'=>$model))) {
        $cs->registerScript('cms-area', <<<EOD

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
            $('.cms-btn-pageunit-create').live('click', function() {
                var type = $(this).attr('id').replace('cms-button-create-', '');
                var area_name = $(this).attr('rel');
                var pageunit_id = $(this).attr('rev');
                var page_id = {$model->id};
                var url = '/?r=page/unitAdd&page_id='+page_id+'&pageunit_id='+pageunit_id+'&area='+area_name+'&type='+type;
                ajaxSave(url, '', 'GET', function(id) {
                    closeDialog();
                    id = jQuery.parseJSON(id);
                    if (pageunit_id != '0') {
                        var pageunit = $('#cms-pageunit-'+pageunit_id);
                    } else {
                        var pageunit = $('#cms-area-'+area_name).find('.cms-empty-area-buttons').eq(0);
                    }
                    pageunit.after('<div id="cms-pageunit-'+id.pageunit_id+'" class="cms-pageunit cms-unit-'+type+'" rel="'+type+'" rev="'+id.unit_id+'"></div>');

                    var orig_bg = $('#cms-pageunit-'+id.pageunit_id).css('backgroundColor');
                    $('#cms-pageunit-'+id.pageunit_id).load('/?r=page/unitView&pageunit_id='+id.pageunit_id+'&id='+page_id+'&language={$language}', function() {
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
            if ($('.pageunit').length == -1)
            {
                var page_id = $('body').attr('rel');
                loadDialog('/?r=page/pageFill&id='+page_id, {
                    ajaxify: true
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

    }

/*
 * Общая панель инструментов для всего сайта
 */
    $txtSureExit = Yii::t('cms', 'Really exit from site edit mode?');
    $fckeditorPath=Yii::app()->params['_path']['fckeditor'] = $am->publish(Yii::getPathOfAlias('application.vendors.fckeditor'));
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
            'edit' => Yii::app()->user->checkAccess('updatePage', array('page'=>$model)) ? array(
                'icon' => 'edit',
                'title' => Yii::t('cms', 'Page properties'),
                'click' => 'js:function(){ pageEditForm(); return false; }',
            ):null,
            'settings' => Yii::app()->user->checkAccess('updateSettings') ? array(
                'icon' => 'settings',
                'title' => Yii::t('cms', 'Site settings'),
                'click' => <<<EOD
js:function(){
            loadDialog('/?r=page/siteSettings&language={$language}');
            return false;
        }
EOD
            ):null,
            'pageadd' => Yii::app()->user->checkAccess('createPage', array('page'=>$model))?array(
                'icon' => 'add',
                'title' => Yii::t('cms', 'Create new page'),
                'click' => 'js:function() { pageAddForm(); return false; }',
            ):null,
            'units'=> Yii::app()->user->checkAccess('manageUnit')?array(
                'icon' => 'large_tiles',
                'title' => Yii::t('cms', 'Units'),
                'click' => <<<EOD
js:function() {
            loadDialog('/?r=page/unitsInstall&language={$language}', {
                ajaxify: true,
                onSave: function() {
                    location.reload();
                }
            });
            return false;
        }
EOD
            ):null,
            'sitemap' =>  Yii::app()->user->checkAccess('createPage', array('page'=>$model))&&Yii::app()->user->checkAccess('updatePage', array('page'=>$model))&&Yii::app()->user->checkAccess('deletePage', array('page'=>$model))?array(
                'icon' => 'sitemap',
                'title' => Yii::t('cms', 'Sitemap'),
                'click' => <<<EOD
js:function(){
            var page_id = {$model->id};
            loadDialog('/?r=page/siteMap&id='+page_id+'&language={$language}', {
                height: Math.ceil($(window).height()*0.5),
                width: 400
            });
            return false;
        }
EOD
            ):null,
            'filemanager' => array(
                'icon' => 'files',
                'title' => Yii::t('cms', 'File manager'),
                'click' => <<<EOD
js:function(){
            var url = '{$fckeditorPath}/editor/plugins/imglib/index.html';
            window.open( url, 'imglib','width=800, height=600, location=0, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes');
            return false;
}
EOD
            ),
            'users' =>  Yii::app()->user->checkAccess('updateUser')?array(
                'icon' => 'user',
                'title' => Yii::t('cms', 'Users'),
                'click' => <<<EOD
js:function() {
            loadDialog('/?r=user/index&language={$language}', {
                simpleClose: true
            });
            return false;
        }
EOD
            ):null,
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
    if (Yii::app()->user->checkAccess('updatePage', array('page'=>$model))) {

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
            'zIndex' => 900,
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
                $('#pageunitpanel').appendTo('body');
                pageunitDeleteDialog(pageunit.attr('rev'), pageunit.attr('id').replace('cms-pageunit-',''), {$model->id});
                return false;
            }
EOD
                ),
            )
        ));
    }

?>

<div class="hidden">

    <div id="cms-pageunit-add" class="cms-splash cms-pageunit-add">
        <h3><?=Yii::t('cms', 'Add unit')?></h3>
        <ul>
        <?php
            $unit_types = Unit::getTypes();
            $unit_types_count = count($unit_types);
            $i=0;
            foreach ($unit_types as $unit_class) {
                $i++;
                $unit_type = Unit::getUnitTypeByClassName($unit_class);
                ?><li><a class="cms-button cms-btn-pageunit-create" id="cms-button-create-<?=$unit_type?>" title="<?=call_user_func(array($unit_class, 'name'))?>" href="#" ><img src="<?=constant($unit_class.'::ICON')?>" alt="<?=call_user_func(array($unit_class, 'name'))?>" /> <?=call_user_func(array($unit_class, 'name'))?></a></li><?php
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