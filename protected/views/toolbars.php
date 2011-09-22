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
            'bookmarks'=>array(
                'icon' => 'bookmark',
                'title'=>Yii::t('cms', 'Bookmarks'),
                'click' => 'js:function() { return false; }',
            ),
            'system'=>array(
                'icon' => 'system',
                'title'=>Yii::t('cms', 'System'),
                'click' => 'js:function() { return false; }',
            ),
            'editmode'=>array(
                'icon' => 'empty',
                'title'=>Yii::t('cms', 'Edit mode'),
                'checked'=>true,
                'click' => <<<EOD
js:function() { 
    var obj = $(this).find('input:checkbox:eq(0)');
    obj.attr('checked', !obj.attr('checked'));
    return false; 
    }
EOD
            ),

            'exit' => array(
                'icon' => 'exit',
                'title' => Yii::t('cms', 'Exit'),
                'cssClass'=>'right',
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
            'edit' => Yii::app()->user->checkAccess('updateContentPage', array('page'=>$model)) ? array(
                'icon' => 'edit',
                'title' => Yii::t('cms', 'Page properties'),
                'click' => 'js:function(){ pageEditForm(); return false; }',
            ):null,
            'pageadd' => Yii::app()->user->checkAccess('createPage', array('page'=>$model))?array(
                'icon' => 'add',
                'title' => Yii::t('cms', 'Create new page'),
                'click' => 'js:function() { pageAddForm(); return false; }',
            ):null,
            'sitemap' =>  Yii::app()->user->checkAccess('createPage', array('page'=>$model))&&Yii::app()->user->checkAccess('updateContentPage', array('page'=>$model))&&Yii::app()->user->checkAccess('deletePage', array('page'=>$model))?array(
                'icon' => 'sitemap',
                'title' => Yii::t('cms', 'Sitemap'),
                'click' => <<<EOD
js:function(){
    var pageId = {$model->id};
    cmsLoadDialog('/?r=admin/siteMap&pageId='+pageId+'&language={$language}', {
        height: Math.ceil($(window).height()*0.5),
        width: 400
    });
    return false;
}
EOD
            ):null,

        ),

    )
);


    $fckeditorPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.vendors.fckeditor'));
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
        'buttons'=>array(
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
        ),

    ));

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
            'settings' => Yii::app()->user->checkAccess('updateSettings') ? array(
                'icon' => 'settings',
                'title' => Yii::t('cms', 'Site settings'),
                'click' => <<<EOD
js:function(){
            cmsLoadDialog('/?r=admin/siteSettings&language={$language}');
            return false;
        }
EOD
            ):null,
            'units'=> Yii::app()->user->checkAccess('manageUnit')?array(
                'icon' => 'units',
                'title' => Yii::t('cms', 'Units'),
                'click' => <<<EOD
js:function() {
            cmsLoadDialog('/?r=unit/install&language={$language}', {
                ajaxify: true,
                onSave: function() {
                    location.reload();
                }
            });
            return false;
        }
EOD
            ):null,
            'users' =>  Yii::app()->user->checkAccess('updateUser')?array(
                'icon' => 'user',
                'title' => Yii::t('cms', 'Users'),
                'click' => <<<EOD
js:function() {
            cmsLoadDialog('/?r=user/index&language={$language}', {
                simpleClose: true
            });
            return false;
        }
EOD
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
                'click' => <<<EOD
js:function() {
    var obj = $(this).find('input:checkbox:eq(0)');
    obj.attr('checked', !obj.attr('checked'));
    return false;
    }
EOD
            ),

        ),

    ));




/*
 * Панель инструментов для блоков
 */
    if (Yii::app()->user->checkAccess('updateContentPage', array('page'=>$model))) {

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
            'iconSize' => 'big',
            'vertical'=>true,
            'rows'=>1,
            'dblclick' => 'js:function() { return false; }',
            'buttons'=>array(
                'add' => array(
                    'icon' => 'add',
                    'title' => Yii::t('cms', 'Add another unit'),
                    'click' => <<<EOD
js:function() {
                cmsShowSelectUnitTypeDialog(this);
                return false;
            }
EOD
                ),
                'edit' => array(
                    'icon' => 'edit',
                    'title' => Yii::t('cms', 'Edit'),
                    'click' => <<<EOD
js:function() {
                var pageUnit = $(this).parents('.cms-pageunit').eq(0);
                pageUnitEditForm(pageUnit);
                return false;
            }
EOD
                ),
                'move' => array(
                    'icon' => 'move',
                    'title' => Yii::t('cms', 'Unit location on pages'),
                    'click' => <<<EOD
js:function() {
                var pageUnit = $(this).parents('.cms-pageunit').eq(0);
                fadeIn(pageUnit, 'selected');
                var pageUnitId = pageUnit.attr('id').replace('cms-pageunit-','');
                var unitId = pageUnit.attr('rev');
                pageUnitSetDialog({$model->id}, pageUnitId, unitId);
                return false;
            }
EOD
                ),
                'up' => array(
                    'icon' => 'up',
                    'title' => Yii::t('cms', 'Move up'),
                    'click' => <<<EOD
js:function() {
                var pageUnit = $(this).parents('.cms-pageunit').eq(0);
                if (pageUnit.prev().length) {
                    pageUnit.insertBefore(pageUnit.prev());
                    area = getAreaByPageUnit(pageUnit);
                    var pageUnitId = pageUnit.attr('id').replace('cms-pageunit-','');
                    ajaxSaveArea(area, getAreaName(area), {$model->id}, 'pageUnitId='+pageUnitId);
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
                var pageUnit = $(this).parents('.cms-pageunit').eq(0);
                if (pageUnit.next().length) {
                    pageUnit.insertAfter(pageUnit.next());
                    area = getAreaByPageUnit(pageUnit);
                    var pageUnitId = pageUnit.attr('id').replace('cms-pageunit-','');
                    ajaxSaveArea(area, getAreaName(area), {$model->id}, 'pageUnitId='+pageUnitId);
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
                var pageUnit = $(this).parents('.cms-pageunit').eq(0);
                fadeIn(pageUnit, 'selected');
                $('#pageunitpanel').appendTo('body');
                pageUnitDeleteDialog(pageUnit.attr('rev'), pageUnit.attr('id').replace('cms-pageunit-',''), {$model->id});
                return false;
            }
EOD
                ),
            )
        ));
    }
