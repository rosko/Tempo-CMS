<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
error_reporting(E_ALL ^ E_NOTICE);
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

    'modules'=>array(
        'install'=>array(
        ),
    ),

    'components'=>array(
        'assetManager'=>array(
            'linkAssets'=>true,
        ),
		'user'=>array(
			'allowAutoLogin'=>true,
            'autoRenewCookie'=>true,
            'loginUrl'=>array('site/login'),
            'returnUrl'=>array('view/index'),
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
        'page'=>array(
            'class'=>'PageComponent',
           
        ),
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
            ),
        ),
        'urlManager'=>array(
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
                        '/?r=site/jsI18N',
                    ),
                ),
            ),
		),
        
	),

), $config);
