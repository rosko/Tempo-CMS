<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

$config = dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php';

$config = is_file($config) ? require($config) : array();

return CMap::mergeArray(array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'defaultController' => 'page',

	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.components.inputs.*',
        'application.extensions.yiidebugtb.*',
	),

    'components'=>array(
		'user'=>array(
			'allowAutoLogin'=>true,
            'loginUrl'=>array('site/login'),
		),
        'settings'=>array(
            'class'=>'Settings'
        ),
        'installer'=>array(
            'class'=>'Installer',
        ),
        'viewRenderer'=>array(
            'class'=>'application.extensions.smarty.ESmartyViewRenderer',
            'fileExtension' => '.tpl',
        ),
        'clientScript'=>array(
            'class'=>'ClientScript',
            'notLoadCoreScriptsOnAjax' => array(
                'jquery',
                'jquery.ui',
                'yiiactiveform',
            ),
            'neededCoreScripts' => array(
                'jquery',
                'jquery.ui',
            ),
            'neededCssFiles' => array(
                '{juiThemeUrl}/{juiTheme}/jquery-ui.css',
                '/3rdparty/jnotify/jquery.jnotify.css',
            ),
            'neededScriptFiles' => array(
                '/3rdparty/jnotify/jquery.jnotify.js',                
            ),
            'neededAdminCoreScripts' => array(
                'yiiactiveform',
            ),
            'neededAdminCssFiles' => array(
                '/3rdparty/fancybox/jquery.fancybox-1.3.1.css',
                '/css/cms.css',
            ),
            'neededAdminScriptFiles' => array(
                '{core}/jui/js/jquery-ui-i18n.min.js',
                '/js/jquery.scrollTo.js',
                '/js/jquery.cookie.js',
                '/js/jquery.hotkeys.js',
                '/3rdparty/fancybox/jquery.fancybox-1.3.1.js',
                '{jsI18N}',
                '/js/lib.js',
            ),
        ),
		'errorHandler'=>array(
            'errorAction'=>'site/error',
        ),
        
	),

), $config);