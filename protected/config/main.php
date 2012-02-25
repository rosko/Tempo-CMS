<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
error_reporting(E_ALL ^ E_NOTICE);
$config = is_file($GLOBALS['local_config']) ? require($GLOBALS['local_config']) : array();

$aliasPattern = '[А-Яа-яA-Za-z0-9-]*';
$urlPattern = '[\/А-Яа-яA-Za-z0-9-]*';

return CMap::mergeArray(array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'defaultController' => 'page',

	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.components.inputs.*',
        'ext.yiidebugtb.*',
	),

    'modules'=>array(
        'install'=>array(
        ),
        'tfilemanager'=>array(
            'import'=>array(
                'application.modules.tfilemanager.components.*',
            ),
        ),
    ),

    'components'=>array(
        'assetManager'=>array(
            'linkAssets'=>true,
        ),
        'authManager'=>array(
            'class'=>'CPhpAuthManager',
            'authFile'=>Yii::getPathOfAlias('local.runtime.auth').'.php',
            'defaultRoles'=>array('anybody', 'guest', 'authenticated'),
        ),
/*        'authManager'=>array(
            'class'=>'AuthManager',
            'connectionID'=>'db',
            'defaultRoles'=>array('anybody', 'guest', 'authenticated'),
            'itemTable'=>$config['components']['db']['tablePrefix'].'auth_item',
            'itemChildTable'=>$config['components']['db']['tablePrefix'].'auth_itemchild',
            'assignmentTable'=>$config['components']['db']['tablePrefix'].'auth_assigment',
            'rightsTable'=>$config['components']['db']['tablePrefix'].'rights',
        ),*/
        'cache'=>array(
            'class'=>'system.caching.CFileCache',
            'directoryLevel'=>1,
        ),
        'clientScript'=>array(
            'class'=>'ClientScript',
            'notLoadCoreScriptsOnAjax' => array(
                'jquery',
                'jquery.ui',
            ),
        ),
		'errorHandler'=>array(
            'errorAction'=>'site/error',
        ),
        'installer'=>array(
            'class'=>'Installer',
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
                        '/?r=site/jsI18N',
                    ),
                ),
            ),
		),
        'page'=>array(
            'class'=>'PageComponent',           
        ),
        'request'=>array(
            'enableCookieValidation'=>true,
            'enableCsrfValidation'=>true,
        ),
        'settings'=>array(
            'class'=>'Settings'
        ),
        'urlManager'=>array(
            'class'=>'UrlManager',
			'urlFormat'=>'path',
            'showScriptName'=>false,
            // Полные или сокращенные адреса страниц
            'fullUrl'=>true,
            'urlSuffix'=>'/',
            'rules'=>array(
                'feed.<type:\w+>'=>array(
                    'site/feed',
                    'urlSuffix'=>'.xml',
                ),
                '<language:[A-Za-z-]+>/feed.<type:\w+>'=>array(
                    'site/feed',
                    'urlSuffix'=>'.xml',
                ),
                'feed/<unittype:\w+>.<type:\w+>'=>array(
                    'site/feed',
                    'urlSuffix'=>'.xml',
                ),
                '<language:[A-Za-z-]+>/feed/<unittype:\w+>.<type:\w+>'=>array(
                    'site/feed',
                    'urlSuffix'=>'.xml',
                ),
                'feed/<unittype:\w+>/<id:\d+>.<type:\w+>'=>array(
                    'site/feed',
                    'urlSuffix'=>'.xml',
                ),
                '<language:[A-Za-z-]+>/feed/<unittype:\w+>/<id:\d+>.<type:\w+>'=>array(
                    'site/feed',
                    'urlSuffix'=>'.xml',
                ),
                'login'=>'site/login',
                'site/captcha'=>'site/captcha',
                "site/login"=>'site/login',
                "site/logout"=>'site/logout',
                'unit/edit'=>'unit/edit',
                'view/unit'=>'view/unit',
                'filesEditor/save'=>'filesEditor/save',
                'users'=>'user/index',

                "<language:[A-Za-z-]+>/<alias:{$aliasPattern}>:<pageId:\d+>"=>'view/index',
                "<alias:{$aliasPattern}>:<pageId:\d+>"=>'view/index',

                "<language:[A-Za-z-]+>/<url:{$urlPattern}>"=>'view/index',
                "<language:[A-Za-z-]+>"=>array(
                    'view/index',
                    'urlSuffix'=>'/',
                ),

                "<language:[A-Za-z-]+>/<alias:{$aliasPattern}>"=>'view/index',
                "<alias:{$aliasPattern}>"=>'view/index',

                "<url:{$urlPattern}>"=>'view/index',

                ""=>'view/index',                
            ),
        ),
		'user'=>array(
            'class'=>'WebUser',
			'allowAutoLogin'=>true,
            'autoRenewCookie'=>true,
            'loginUrl'=>array('site/login'),
            'returnUrl'=>array('view/index'),
		),        
        'viewRenderer'=>array(
            'class'=>'application.extensions.smarty.ESmartyViewRenderer',
            'fileExtension' => '.tpl',
        ),
	),
    'params'=>array(
        'aliasPattern'=>$aliasPattern,

    ),
), $config);
