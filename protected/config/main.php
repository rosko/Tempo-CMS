<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
$config = is_file($GLOBALS['local']) ? require($GLOBALS['local']) : array();

return CMap::mergeArray(array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'defaultController' => 'page',

	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.components.inputs.*',
	),

    'components'=>array(
		'user'=>array(
			'allowAutoLogin'=>true,
            'loginUrl'=>array('site/login'),
            'returnUrl'=>array('page/view'),
		),
        'request'=>array(
            'enableCookieValidation'=>true,
            'enableCsrfValidation'=>true,
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
                '{jnotify}/jquery.jnotify.css',
            ),
            'neededScriptFiles' => array(
                '{jnotify}/jquery.jnotify.js',
            ),
            'neededAdminCoreScripts' => array(
                'yiiactiveform',
            ),
            'neededAdminCssFiles' => array(
                '{fancybox}/jquery.fancybox-1.3.1.css',
                '{css}/cms.css',
            ),
            'neededAdminScriptFiles' => array(
                '{core}/jui/js/jquery-ui-i18n.min.js',
                '{js}/jquery.scrollTo.js',
                '{js}/jquery.cookie.js',
                '{js}/jquery.hotkeys.js',
                '{fancybox}/jquery.fancybox-1.3.1.js',
                '{jsI18N}',
                '{js}/lib.js',
            ),
        ),
        'urlManager'=>array(
            'rules'=>array(
                "site/login"=>'site/login',
                "site/logout"=>'site/logout',
                'filesEditor/save'=>'filesEditor/save',

            ),
        ),
		'errorHandler'=>array(
            'errorAction'=>'site/error',
        ),
        
	),

), $config);
