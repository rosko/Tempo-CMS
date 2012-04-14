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

$unitConfig = ContentUnit::loadConfig();
if (Yii::app()->user->hasState('askfill') && isset($unitConfig['register']))  {

    $registerModel = ModelRegister::model()->find('widget_id > 0');
    if ($registerModel) {
        $shortMessage = '<a href=\''.$registerModel->getWidgetUrl().'\'>'.Yii::t('cms', 'Please fill all required fields in your personal profile. And if necessary, change your password.').'</a>';
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
    $addjs = "$('body').scrollTo(pageWidget, 800);";
} else {
    $addjs = '';
}

$js = <<<JS
$('.ajaxPager a').live('click', function() {
    var pageWidget = $(this).parents('.pagewidget').eq(0);
    if (pageWidget.length) {
        var pos = $(this).attr('href').indexOf('?');
        var data = '';
        if (pos > -1) {
            data = $(this).attr('href').substr(pos+1);
        }
        var pageWidgetId = pageWidget.attr('id').replace('cms-pagewidget-','');
        cmsAddToLocationHash(data, false, pageWidget.attr('rel')+pageWidget.attr('content_id')+'_page');
        $('#pagewidgetpanel').appendTo('body');
        cmsReloadPageWidget(pageWidgetId, '.pagewidget[rev='+pageWidget.attr('rev')+']', function() {
            {$addjs}
        }, '&'+data);
    }
    return false;
});
JS;
$cs->registerScript('ajaxPager', $js, CClientScript::POS_READY);

if (!Yii::app()->user->isGuest) {

    $cs->registerPackage('cmsAdmin');
    $cs->registerPackage('jstree');

    if (!$model->active) {
        $this->pageTitle = '['.Yii::t('cms', 'Page unactive').'] ' . $this->pageTitle;
    }

    if (!Yii::app()->user->isGuest) {
        $cs->registerScript('cms-area', <<<JS

            // Настройки и обработчики перещения юнитов на странице
            $('.cms-area').sortable({
                connectWith: '.cms-area',
                placeholder: 'cms-pagewidget-highlight',
                revert: true,
                opacity:1,
                forcePlaceholderSize:true,
                cancel:'.cms-pagewidget-menu,.cms-empty-area-buttons',
                update:function(event, ui) {
                    var pageWidgetId = $(ui.item).attr('id').replace('cms-pagewidget-','');
                    var areaName = cmsGetAreaNameByPageWidget(ui.item);
                    if (ui.sender) {
                        // Запрос на обновление предыдущей области + перемещенному элементу указывается новое значение в area
    //                    var old_area = $(ui.sender).attr('id').replace('cms-area-', '');
    //                    cmsAjaxSaveArea($(ui.sender), areaName, {$model->id}, 'pageWidgetId='+pageWidgetId+'&old_area='+old_area);

                    } else {
                        // Запрос на обновление текущей области
                        cmsAjaxSaveArea(cmsGetAreaByPageWidget(ui.item), areaName, {$model->id}, 'pageWidgetId='+pageWidgetId);
                    }
                },
                start:function(event, ui) {
                    $(ui.helper).find('.cms-panel').hide();
                    $('.cms-area').addClass('cms-potential');
                    $('.cms-area').each(function() {
                        if ($(this).find('.cms-pagewidget').length == 0)
                            $(this).addClass('cms-empty-area');
                    });
                    cmsAreaEmptyCheck();
                },
                stop:function(event, ui) {
                    $('.cms-area').removeClass('cms-potential').removeClass('cms-empty-area');
                    cmsAreaEmptyCheck();
                }
            }).disableSelection();

            $('.cms-pagewidget').css('cursor', 'move');

            cmsAreaEmptyCheck();
            $('.cms-pagewidget').live('mouseenter', function() {
                $(this).addClass('cms-hover');
            }).live('mouseleave', function() {
                $(this).removeClass('cms-hover');
            });

            $('.cms-pagewidget').live('dblclick', function() {
                cmsClearSelection();
                cmsPageWidgetEditForm(this);
                return false;
            })

            // Обработчик для выбора типа юнита при создании
            $('.cms-btn-pagewidget-create').live('click', function() {
                var widgetClass = $(this).attr('id').replace('cms-button-create-', '');
                var areaName = $(this).attr('rel');
                var prevPageWidgetId = $(this).attr('rev');
                var pageId = $('body').attr('rel');
                var url = '/?r=widget/edit&pageId='+pageId+'&prevPageWidgetId='+prevPageWidgetId+'&area='+areaName+'&widgetClass='+widgetClass+'&return=html&language='+$.data(document.body, 'language');
                cmsLoadDialog(url, {
                    simpleClose: false
                });
                return false;
            });

            // Отображение диалога "Заполнить страницу" на пустой странице
            if ($('.pagewidget').length == -1)
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

<div class="cms-hidden">

    <div id="cms-pagewidget-add" class="cms-splash cms-pagewidget-add">
        <h3><?=Yii::t('cms', 'Add widget')?></h3>
        <ul>
        <?php
        $units = ContentWidget::getInstalledWidgets();
        $units_count = count($units);
        $i = 0;
        foreach ($units as $unit) {
            $i++;
            ?><li> <ul><?php
            foreach ($unit['widgets'] as $widget) {

                $modelClassName = call_user_func(array($widget['className'],'modelClassName'));
                $model = CActiveRecord::model($modelClassName);
                if (!$model->isMaxLimitReached()) {

                    ?><li><a class="cms-button cms-btn-pagewidget-create" id="cms-button-create-<?=$widget['className']?>" title="<?=$widget['name']?>" href="#" ><img src="<?=$widget['icon']?>" alt="<?=$widget['name']?>" /> <?=$widget['name']?></a></li><?php

                }
            }
            ?></ul></li><?php
            if ($i == ceil($units_count/2)) {
                ?></ul><ul><?php
            }
        }
        ?>
        </ul>
    </div>

    <div id="cms-pagewidget-edit">
    </div>

    <div id="cms-pagewidget-delete" class="cms-splash">
    </div>

    <div id="cms-pagewidget-set" class="cms-splash">
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