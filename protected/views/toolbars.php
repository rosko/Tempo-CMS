<?php
/*
 * Общая панель инструментов для всего сайта
 */
// $menuCssClass = 'aristo-menu';
// $menuCssFile = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.css')).'/aristo-menu.css';

$menuCssClass = 'cms-topbar';
$menuCssFile = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.css')).'/topbar.css';

    $txtSureExit = Yii::t('cms', 'Really exit from site edit mode?');
    $this->widget('Toolbar', array(
        'id' => 'toolbar',
        'cssClass'=>$menuCssClass,
        'cssFile'=>$menuCssFile,
        'location' => array(
            'selector' => 'body',
            'position' => array('absolute', 'top', 'wide'),
            'show' => 'always',
            //'draggable' => true,
            //'resizable' => true,
            //'save' => true,
        ),
        'iconSize'=>'small',
        'showTitles'=>true,
        'vertical'=>false,
        'rows'=>1,
        'buttons'=>array(
            'pages'=>array(
                'icon' => 'page',
                'title'=>Yii::t('cms', 'Pages'),
                'click' => 'js:function() { return false; }',
            ),
            'info'=>array(
                'icon' => 'data',
                'title'=>Yii::t('cms', 'Information'),
                'click' => 'js:function() { return false; }',
            ),
/*            'bookmarks'=>array(
                'icon' => 'bookmark',
                'title'=>Yii::t('cms', 'Bookmarks'),
                'click' => 'js:function() { return false; }',
            ),*/
            'system'=>array(
                'icon' => 'system',
                'title'=>Yii::t('cms', 'System'),
                'click' => 'js:function() { return false; }',
            ),
            'editmode'=>array(
                'icon' => 'empty',
                'title'=>Yii::t('cms', 'Edit mode'),
                'checked'=>true,
                'click' => 'js:function()'.<<<JS
 {
    var obj = $(this).find('input:checkbox:eq(0)');
    obj.attr('checked', !obj.attr('checked'));
    return false; 
    }
JS
            ),

            'exit' => array(
                'icon' => 'exit',
                'title' => Yii::t('cms', 'Exit'),
                'cssClass'=>'right',
                'click' => 'js:function()'.<<<JS
{
    if (confirm('{$txtSureExit}')) {
        location.href = '{$this->createUrl('site/logout')}';
    }
    return false;
}
JS
            ),
        ),
    ));


    $this->widget('Toolbar', array(
        'id' => 'pages_menu',
        'cssClass'=>$menuCssClass,
        'cssFile'=>$menuCssFile,
        'location' => array(
            'selector' => '#toolbar_pages_li',
            'position' => array('outter', 'bottom', 'left'),
            'show' => 'hover',
            //'draggable' => false,
            //'resizable' => true,
            //'save' => true,
        ),
//        'functionShow'=>'slideDown()',
//        'functionHide'=>'slideUp()',
        'iconSize' => 'small',
        'showTitles'=>true,
        'vertical'=>true,
        'rows'=>1,
        'dblclick' => 'js:function() { return false; }',
        'buttons'=>array(
            'edit' => !Yii::app()->user->isGuest ? array(
                'icon' => 'edit',
                'title' => Yii::t('cms', 'Page properties'),
                'click' => 'js:function(){ cmsPageEditForm(); return false; }',
            ):null,
            'pageadd' => !Yii::app()->user->isGuest ? array(
                'icon' => 'add',
                'title' => Yii::t('cms', 'Create new page'),
                'click' => 'js:function() { cmsPageAddForm(); return false; }',
            ):null,
            'sitemap' =>  !Yii::app()->user->isGuest ? array(
                'icon' => 'sitemap',
                'title' => Yii::t('cms', 'Sitemap'),
                'click' => 'js:function()'.<<<JS
{
    var pageId = {$model->id};
    cmsLoadDialog('/?r=admin/siteMap&pageId='+pageId+'&language={$language}', {
        height: Math.ceil($(window).height()*0.5),
        width: 400
    });
    return false;
}
JS
            ):null,

        ),

    ));

    $buttons = array(
        'filemanager' => array(
            'icon' => 'files',
            'title' => Yii::t('cms', 'File manager'),
            'click' => 'js:function()'.<<<JS
{
            var url = '/?r=files/manager&language={$language}';
            window.open( url, 'filemanager','width=950, height=550, location=0, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes');
            return false;
}
JS
        ),
    );
    $units = ContentUnit::getInstalledUnits(true);
    $modelToolbars = '';
    foreach ($units as $unitClassName => $unitName) {
        $widgets = call_user_func(array($unitClassName, 'widgets'));
        $models = array();
        foreach ($widgets as $widgetClassName) {
            $models[] = call_user_func(array($widgetClassName, 'modelClassName'));
        }
        $models = array_diff(call_user_func(array($unitClassName, 'models')), $models);
        foreach ($models as $modelClassName) {

            $buttons[$modelClassName] = array(
                'icon' => 'data',
                'title' => call_user_func(array($modelClassName, 'modelName')),
                'click' => 'js:function()'. <<<JS
{
                        cmsLoadDialog('/?r=records/list&className={$modelClassName}&language={$language}');
                        return false;
}
JS
            );
            $modelToolbars .= $this->widget('Toolbar', array(
                    'id' => 'info_'.$modelClassName,
                    'cssClass' => $menuCssClass,
                    'cssFile' => $menuCssFile,
                    'location' => array(
                        'selector' => '#info_menu_'.$modelClassName.'_li',
                        'position' => array('outter', 'right', 'top'),
                        'show' => 'hover',
                        'draggable' => false,
                    ),
                    'iconSize' => 'small',
                    'showTitles' => true,
                    'vertical' => true,
                    'rows' => 1,
                    'dblclick' => 'js:function() { return false; }',
                    'buttons' => array(
                        'add' => array(
                            'icon' => 'add',
                            'title' => Yii::t('cms', 'Add'),
                            'click' => 'js:function()'. <<<JS
{
                                    cmsRecordEditForm(0, '{$modelClassName}', 0);
                                    return false;
}
JS
                        ),
                        'list' => array(
                            'icon' => 'list',
                            'title' => Yii::t('cms', 'List all'),
                            'click' => 'js:function()'. <<<JS
{
                                    cmsLoadDialog('/?r=records/list&className={$modelClassName}&language={$language}');
                                    return false;
}
JS
                        ),
                    ),
                ), true
            );
        }
    }

    $this->widget('Toolbar', array(
        'id' => 'info_menu',
        'cssClass'=>$menuCssClass,
        'cssFile'=>$menuCssFile,
        'location' => array(
            'selector' => '#toolbar_info_li',
            'position' => array('outter', 'bottom', 'left'),
            'show' => 'hover',
            'draggable' => false,
            //'resizable' => true,
            //'save' => true,
        ),
        'iconSize' => 'small',
        'showTitles'=>true,
        'vertical'=>true,
        'rows'=>1,
        'dblclick' => 'js:function() { return false; }',
        'buttons' => $buttons,

    ));
    echo $modelToolbars;
/*
    $this->widget('Toolbar', array(
        'id' => 'bookmarks_menu',
        'cssClass'=>$menuCssClass,
        'cssFile'=>$menuCssFile,
        'location' => array(
            'selector' => '#toolbar_bookmarks_li',
            'position' => array('outter', 'bottom', 'left'),
            'show' => 'hover',
            'draggable' => false,
            //'resizable' => true,
            //'save' => true,
        ),
        'iconSize' => 'small',
        'showTitles'=>true,
        'vertical'=>true,
        'rows'=>1,
        'dblclick' => 'js:function() { return false; }',
        'buttons'=>array(
        ),

    ));
*/
    $this->widget('Toolbar', array(
        'id' => 'system_menu',
        'cssClass'=>$menuCssClass,
        'cssFile'=>$menuCssFile,
        'location' => array(
            'selector' => '#toolbar_system_li',
            'position' => array('outter', 'bottom', 'left'),
            'show' => 'hover',
            'draggable' => false,
            //'resizable' => true,
            //'save' => true,
        ),
        'iconSize' => 'small',
        'showTitles'=>true,
        'vertical'=>true,
        'rows'=>1,
        'dblclick' => 'js:function() { return false; }',
        'buttons'=>array(
            'settings' => !Yii::app()->user->isGuest ? array(
                'icon' => 'settings',
                'title' => Yii::t('cms', 'Site settings'),
                'click' => 'js:function()'.<<<JS
{
            cmsLoadDialog('/?r=admin/siteSettings&language={$language}');
            return false;
        }
JS
            ):null,
            'rights' => !Yii::app()->user->isGuest ? array(
                'icon' => 'safe',
                'title' => Yii::t('cms', 'Access rights'),
                'click' => 'js:function()'.<<<JS
{
            cmsLoadDialog('/?r=admin/rights&language={$language}');
            return false;
        }
JS
            ):null,
            'units'=> !Yii::app()->user->isGuest ? array(
                'icon' => 'units',
                'title' => Yii::t('cms', 'Units'),
                'click' => 'js:function()'.<<<JS
 {
    cmsLoadDialog('/?r=unit/install&language={$language}', {
        ajaxify: true,
        onSave: function() {
            location.reload();
        }
    });
    return false;
}
JS
            ):null,
            'users' =>  !Yii::app()->user->isGuest ? array(
                'icon' => 'user',
                'title' => Yii::t('cms', 'Users'),
                'click' => 'js:function()'.<<<JS
 {
    cmsLoadDialog('/?r=records/list&className=User&language={$language}', {
        simpleClose: true
    });
    return false;
}
JS
            ):null,
            'roles' =>  !Yii::app()->user->isGuest ? array(
                'icon' => 'user',
                'title' => Yii::t('cms', 'User roles'),
                'click' => 'js:function()'.<<<JS
 {
    cmsLoadDialog('/?r=records/list&className=Role&language={$language}', {
        simpleClose: true
    });
    return false;
}
JS
            ):null,
        ),

    ));

    $this->widget('Toolbar', array(
        'id' => 'editmode_menu',
        'cssClass'=>$menuCssClass,
        'cssFile'=>$menuCssFile,
        'location' => array(
            'selector' => '#toolbar_editmode_li',
            'position' => array('outter', 'bottom', 'left'),
            'show' => 'hover',
            'draggable' => false,
            //'resizable' => true,
            //'save' => true,
        ),
        'iconSize' => 'small',
        'showTitles'=>true,
        'vertical'=>true,
        'rows'=>1,
        'dblclick' => 'js:function() { return false; }',
        'buttons'=>array(

            'editmode'=>array(
                'icon' => 'empty',
                'title'=>Yii::t('cms', 'Edit mode'),
                'checked'=>true,
                'click' => 'js:function()'.<<<JS
 {
    var obj = $(this).find('input:checkbox:eq(0)');
    obj.attr('checked', !obj.attr('checked'));
    return false;
}
JS
            ),

        ),

    ));




/*
 * Панель инструментов для блоков
 */
    if (!Yii::app()->user->isGuest) {

        $this->widget('Toolbar', array(
            'id' => 'pagewidgetpanel',
            'location' => array(
                'selector' => '.cms-pagewidget',
                'position' => array('outter', 'left', 'top'),
                'show' => 'hover',
                'draggable' => false,
                //'resizable' => true,
                //'save' => true,
            ),
            'iconSize' => 'big',
            'vertical'=>true,
            'rows'=>1,
            'dblclick' => 'js:function() { return false; }',
            'buttons'=>array(
                'add' => array(
                    'icon' => 'add',
                    'title' => Yii::t('cms', 'Add another widget'),
                    'click' => 'js:function()'.<<<JS
 {
    cmsShowSelectWidgetDialog(this);
    return false;
}
JS
                ),
                'edit' => array(
                    'icon' => 'edit',
                    'title' => Yii::t('cms', 'Edit'),
                    'click' => 'js:function()'.<<<JS
 {
    var pageWidget = $(this).parents('.cms-pagewidget').eq(0);
    if (pageWidget.data('editParams')) {
        if (pageWidget.data('editParams').modelClass && pageWidget.data('editParams').recordId) {
            cmsRecordEditForm(
                pageWidget.data('editParams').recordId,
                pageWidget.data('editParams').modelClass,
                pageWidget.attr('rev'),
                null);
            return false;
        }
    }
    cmsPageWidgetEditForm(pageWidget);
    return false;
}
JS
                ),
                'move' => array(
                    'icon' => 'move',
                    'title' => Yii::t('cms', 'Widget location on pages'),
                    'click' => 'js:function()'.<<<JS
 {
                var pageWidget = $(this).parents('.cms-pagewidget').eq(0);
                cmsFadeIn(pageWidget, 'cms-selected');
                var pageWidgetId = pageWidget.attr('id').replace('cms-pagewidget-','');
                var widgetId = pageWidget.attr('rev');
                cmsPageWidgetSetDialog({$model->id}, pageWidgetId, widgetId);
                return false;
            }
JS
                ),
                'up' => array(
                    'icon' => 'up',
                    'title' => Yii::t('cms', 'Move up'),
                    'click' => 'js:function()'.<<<JS
 {
    var pageWidget = $(this).parents('.cms-pagewidget').eq(0);
    if (pageWidget.prev().length) {
        pageWidget.insertBefore(pageWidget.prev());
        area = cmsGetAreaByPageWidget(pageWidget);
        var pageWidgetId = pageWidget.attr('id').replace('cms-pagewidget-','');
        cmsAjaxSaveArea(area, cmsGetAreaName(area), {$model->id}, 'pageWidgetId='+pageWidgetId);
    }
    return false;
}
JS
                ),
                'down' => array(
                    'icon' => 'down',
                    'title' => Yii::t('cms', 'Move down'),
                    'click' => 'js:function()'.<<<JS
 {
    var pageWidget = $(this).parents('.cms-pagewidget').eq(0);
    if (pageWidget.next().length) {
        pageWidget.insertAfter(pageWidget.next());
        area = cmsGetAreaByPageWidget(pageWidget);
        var pageWidgetId = pageWidget.attr('id').replace('cms-pagewidget-','');
        cmsAjaxSaveArea(area, cmsGetAreaName(area), {$model->id}, 'pageWidgetId='+pageWidgetId);
    }
    return false;
}
JS
                ),
                'delete' => array(
                    'icon' => 'delete',
                    'title' => Yii::t('cms', 'Delete the widget'),
                    'click' => 'js:function()'.<<<JS
 {
    var pageWidget = $(this).parents('.cms-pagewidget').eq(0);
    cmsFadeIn(pageWidget, 'cms-selected');
    $('#pagewidgetpanel').appendTo('body');
    cmsPageWidgetDeleteDialog(pageWidget.attr('rev'), pageWidget.attr('id').replace('cms-pagewidget-',''), {$model->id});
    return false;
}
JS
                ),
            )
        ));
    }
