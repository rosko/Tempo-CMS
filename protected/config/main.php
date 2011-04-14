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
        'ext.yiidebugtb.*',
	),

    'components'=>array(
        'assetManager'=>array(
            'linkAssets'=>true,
        ),
		'user'=>array(
			'allowAutoLogin'=>true,
            'autoRenewCookie'=>true,
            'loginUrl'=>array('site/login'),
            'returnUrl'=>array('page/view'),
		),
/*        'authManager'=>array(
            'class'=>'AuthManager',
            'connectionID'=>'db',
            'defaultRoles'=>array('anybody', 'guest', 'authenticated'),
            'itemTable'=>$config['components']['db']['tablePrefix'].'auth_item',
            'itemChildTable'=>$config['components']['db']['tablePrefix'].'auth_itemchild',
            'assignmentTable'=>$config['components']['db']['tablePrefix'].'auth_assigment',
        ),*/
        'authManager'=>array(
            'class'=>'CPhpAuthManager',
            'authFile'=>Yii::getPathOfAlias('local.runtime.auth').'.php',
            'defaultRoles'=>array('anybody', 'guest', 'authenticated'),
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
                'page/unitView'=>'page/unitView',
                'login'=>'site/login',
                'site/captcha'=>'site/captcha',
                "site/login"=>'site/login',
                "site/logout"=>'site/logout',
                'filesEditor/save'=>'filesEditor/save',
                'users'=>'user/index',
                'rights'=>'rights',
                

            ),
        ),
		'errorHandler'=>array(
            'errorAction'=>'site/error',
        ),
		'log'=>array(
			'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'XWebDebugRouter',
                    'config'=>'alignLeft, opaque, runInDebug, yamlStyle',
                    'levels'=>'error, warning, trace, profile, info',
                    'allowedIPs'=>array('127.0.0.1'),
                    'restrictedUris'=>array(
                        '/?r=page/jsI18N',
                    ),
                ),
            ),
		),
        
	),

), $config);
