<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

$config = is_file($GLOBALS['local_config']) ? require($GLOBALS['local_config']) : array();

return CMap::mergeArray(array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'defaultController' => 'page',

	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.components.inputs.*',
	),

    'components'=>array(
        'assetManager'=>array(
            'linkAssets'=>true,
        ),
		'user'=>array(
			'allowAutoLogin'=>true,
            'loginUrl'=>array('site/login'),
            'returnUrl'=>array('page/view'),
		),
        'authManager'=>array(
            'class'=>'AuthManager',
            'connectionID'=>'db',
            'defaultRoles'=>array('guest','authenticated'),
            'itemTable'=>$config['components']['db']['tablePrefix'].'auth_item',
            'itemChildTable'=>$config['components']['db']['tablePrefix'].'auth_itemchild',
            'assignmentTable'=>$config['components']['db']['tablePrefix'].'auth_assigment',
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
                '{js}/jquery.scrollTo.js',
                '{js}/lib.js',
                '{jsI18N}',
            ),
            'neededAdminCoreScripts' => array(
                'yiiactiveform',
            ),
            'neededAdminCssFiles' => array(
                '{topbox}/css/topbox.css',
                '{css}/cms.css',
            ),
            'neededAdminScriptFiles' => array(
                '{core}/jui/js/jquery-ui-i18n.min.js',
                '{js}/jquery.cookie.js',
                '{js}/jquery.hotkeys.js',
                '{topbox}/js/topbox.js',
                '{js}/dialogs.js',
                '{js}/cms.js',
            ),
        ),
        'urlManager'=>array(
            'rules'=>array(
                'page/unitForm'=>'page/unitForm',
                'login'=>'site/login',
                "site/login"=>'site/login',
                "site/logout"=>'site/logout',
                'filesEditor/save'=>'filesEditor/save',
                'users'=>'user/index',
                

            ),
        ),
		'errorHandler'=>array(
            'errorAction'=>'site/error',
        ),
        
	),

), $config);
