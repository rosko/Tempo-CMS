<?php
$this->pageTitle = $model->title;
$language = Yii::app()->language;

$cs=Yii::app()->getClientScript();
$am=Yii::app()->getAssetManager();
$cs->registerPackage('cmsLib');

$csrfTokenName = Yii::app()->getRequest()->csrfTokenName;
$csrfToken = Yii::app()->getRequest()->getCsrfToken();

$title = str_replace("'", "\\'", $model->title);

$cs->registerScript('all', <<<JS

        $('body').attr('rel', {$model->id});
        $.data(document.body, 'title', '{$title}');
        $.data(document.body, 'language', '{$language}');
        $.data(document.body, 'csrfTokenName', '{$csrfTokenName}');
        $.data(document.body, 'csrfToken', '{$csrfToken}');

//        $('input[name={$csrfTokenName}]').val('{$csrfToken}');

        window.setInterval(function() { cmsProcessLocationHash(); }, 100);

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


JS
, CClientScript::POS_READY);

$js = '';
$flashes = Yii::app()->user->getFlashes(false);

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
    $js .= "cmsNotify({$flash}, {$options});\n";
}
if ($js)
    $cs->registerScript('flashes', $js, CClientScript::POS_READY);

if (Yii::app()->settings->getValue('ajaxPagerScroll')) {
    $addjs = "$('body').scrollTo(pageUnit, 800);";
} else {
    $addjs = '';
}

$js = <<<JS
$('.ajaxPager a').live('click', function() {
    var pageUnit = $(this).parents('.pageunit').eq(0);
    if (pageUnit.length) {
        var pos = $(this).attr('href').indexOf('?');
        var data = '';
        if (pos > -1) {
            data = $(this).attr('href').substr(pos+1);
        }
        var pageUnitId = pageUnit.attr('id').replace('cms-pageunit-','');
        cmsAddToLocationHash(data, false, pageUnit.attr('rel')+pageUnit.attr('content_id')+'_page');
        $('#pageunitpanel').appendTo('body');
        cmsReloadPageUnit(pageUnitId, '.pageunit[rev='+pageUnit.attr('rev')+']', function() {
            {$addjs}
        }, '&'+data);
    }
    return false;
});
JS;
$cs->registerScript('ajaxPager', $js, CClientScript::POS_READY);

if (Yii::app()->user->checkAccess('createPage', array('page'=>$model)) ||
    Yii::app()->user->checkAccess('updateContentPage', array('page'=>$model)) ||
    Yii::app()->user->checkAccess('deletePage', array('page'=>$model))) {

    $cs->registerPackage('cmsAdmin');
    $cs->registerPackage('jstree');

    if (!$model->active) {
        $this->pageTitle = '['.Yii::t('cms', 'Page unactive').'] ' . $this->pageTitle;
    }

    if (Yii::app()->user->checkAccess('updateContentPage', array('page'=>$model))) {
        $cs->registerScript('cms-area', <<<JS

            // Настройки и обработчики перещения юнитов на странице
            $('.cms-area').sortable({
                connectWith: '.cms-area',
                placeholder: 'cms-pageunit-highlight',
                revert: true,
                opacity:1,
                forcePlaceholderSize:true,
                cancel:'.cms-pageunit-menu,.cms-empty-area-buttons',
                update:function(event, ui) {
                    var pageUnitId = $(ui.item).attr('id').replace('cms-pageunit-','');
                    var areaName = cmsGetAreaNameByPageUnit(ui.item);
                    if (ui.sender) {
                        // Запрос на обновление предыдущей области + перемещенному элементу указывается новое значение в area
    //                    var old_area = $(ui.sender).attr('id').replace('cms-area-', '');
    //                    cmsAjaxSaveArea($(ui.sender), areaName, {$model->id}, 'pageUnitId='+pageUnitId+'&old_area='+old_area);

                    } else {
                        // Запрос на обновление текущей области
                        cmsAjaxSaveArea(cmsGetAreaByPageUnit(ui.item), areaName, {$model->id}, 'pageUnitId='+pageUnitId);
                    }
                },
                start:function(event, ui) {
                    $(ui.helper).find('.cms-panel').hide();
                    $('.cms-area').addClass('potential');
                    $('.cms-area').each(function() {
                        if ($(this).find('.cms-pageunit').length == 0)
                            $(this).addClass('cms-empty-area');
                    });
                    cmsAreaEmptyCheck();
                },
                stop:function(event, ui) {
                    $('.cms-area').removeClass('potential').removeClass('cms-empty-area');
                    cmsAreaEmptyCheck();
                }
            }).disableSelection();

            $('.cms-pageunit').css('cursor', 'move');

            cmsAreaEmptyCheck();
            $('.cms-pageunit').live('mouseenter', function() {
                $(this).addClass('hover');
            }).live('mouseleave', function() {
                $(this).removeClass('hover');
            });

            $('.cms-pageunit').live('dblclick', function() {
                cmsClearSelection();
                cmsPageUnitEditForm(this);
                return false;
            })

            // Обработчик для выбора типа юнита при создании
            $('.cms-btn-pageunit-create').live('click', function() {
                var type = $(this).attr('id').replace('cms-button-create-', '');
                var areaName = $(this).attr('rel');
                var prevPageUnitId = $(this).attr('rev');
                var pageId = $('body').attr('rel');
                var url = '/?r=unit/edit&pageId='+pageId+'&prevPageUnitId='+prevPageUnitId+'&area='+areaName+'&type='+type+'&return=html&language='+$.data(document.body, 'language');
                cmsLoadDialog(url, {
                    simpleClose: false
                });
                return false;
            });

            // Отображение диалога "Заполнить страницу" на пустой странице
            if ($('.pageunit').length == -1)
            {
                var pageId = $('body').attr('rel');
                cmsLoadDialog('/?r=page/fill&pageId='+pageId, {
                    ajaxify: true
                });
            }

JS
    , CClientScript::POS_READY);


        if (Yii::app()->settings->getValue('autoSave')) {

            $cs->registerScript('autoSave', <<<JS
                setInterval(function() {
                    $('form input[name=apply]:submit').each(function() {
                        $(this).parents('form').attr('rev', 'apply').trigger('submit');
                    });
                }, 30000);
JS
            , CClientScript::POS_READY);

        }

    }

$this->renderPartial('/toolbars', compact('model', 'language'));

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
                ?><li><a class="cms-button cms-btn-pageunit-create" id="cms-button-create-<?=$unit_type?>" title="<?=call_user_func(array($unit_class, 'unitName'))?>" href="#" ><img src="<?=constant($unit_class.'::ICON')?>" alt="<?=call_user_func(array($unit_class, 'unitName'))?>" /> <?=call_user_func(array($unit_class, 'unitName'))?></a></li><?php
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