<?php

$_packages = array(
    'cmsUnits'=>array(
        'basePath'=>'application.assets',
        'js'=>array('js/units.js'),
        'depends'=>array('jquery', 'cmsDialogs'),
    ),
    'cmsDialogs'=>array(
        'basePath'=>'application.assets',
        'js'=>array('js/dialogs.js'),
        'depends'=>array('jquery', 'jquery.ui', 'jquery.uicss', 'topbox', 'cmsLib'),
    ),
    'topbox'=>array(
        'basePath'=>'application.vendors.topbox',
        'js'=>array('js/topbox.js'),
        'css'=>array('css/topbox.css'),
        'depends'=>array('jquery'/*,'jquery.nanoscroller'*/),
    ),
    'cmsLib'=>array(
        'basePath'=>'application.assets',
        'js'=>array('js/lib.js'),
        'css'=>array('css/cms.css', 'css/form.css'),
        'depends'=>array('jquery', 'cmsJs18N', 'jnotify', 'scrollTo'),
    ),
    'cmsAdmin'=>array(
        'basePath'=>'application.assets',
        'js'=>array('js/cms.js'),
        'depends'=>array('jquery', 'jquery.ui', 'jquery.uicss', 'hotkeys', 'cookie', 'cmsLib', 'cmsUnits'),
    ),
    'cmsJs18N'=>array(
        'baseUrl'=>'/',
        'js'=>array('?r=site/jsI18N&language='.Yii::app()->language),
    ),
    'jnotify'=>array(
        'basePath'=>'application.vendors.jnotify',
        'js'=>array('jquery.jnotify.js'),
        'css'=>array('jquery.jnotify.css'),
        'depends'=>array('jquery', 'jquery.ui'),
    ),
    'jquery.uicss'=>array(
        'basePath'=> 'application.assets.css.jui',
        'css'=>array(Yii::app()->params['juiTheme'].'/jquery-ui.css'),
        'depends'=>array('jquery', 'jquery.ui'),
    ),
    'scrollTo'=>array(
        'basePath'=>'application.assets',
        'js'=>array('js/jquery.scrollTo.js'),
        'depends'=>array('jquery'),
    ),
    'hotkeys'=>array(
        'basePath'=>'application.assets',
        'js'=>array('js/jquery.hotkeys.js'),
        'depends'=>array('jquery'),
    ),
    'jstree'=>array(
        'basePath'=>'ext.jsTree.source',
        'js'=>array('jquery.jstree.js'),
        'depends'=>array('jquery'),
    ),
    'jquery.nanoscroller'=>array(
        'basePath'=>'application.vendors.jquery-nanoscroller',
        'js'=>array('jquery.nanoscroller.min.js'),
        'css'=>array('nanoscroller.css'),
        'depends'=>array('jquery'),        
    ),
);

if (!empty(Yii::app()->params['juiThemeUrl'])) {
    $_packags['jquery.uics']['baseUrl'] = Yii::app()->params['juiThemeUrl'];
}

return $_packages;