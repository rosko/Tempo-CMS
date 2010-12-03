<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'language'=>'ru', // Скопировать эту настройку в БД, когда будем делать мультиязычность
    'defaultController' => 'page',

//	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.components.inputs.*',
        'application.extensions.yiidebugtb.*',
	),

    'modules'=>array(
        'maintain'=>array(
            'password'=>'admin',
        ),
    ),

	// application components
	'components'=>array(
//        'cache'=>array(
//            'class'=>'system.caching.CFileCache'
//        ),
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
        'settings'=>array(
            'class'=>'Settings'
        ),
        'viewRenderer'=>array(
            'class'=>'application.extensions.smarty.ESmartyViewRenderer',
            'fileExtension' => '.tpl',
            //'pluginsDir' => 'application.smartyPlugins',
            //'configDir' => 'application.smartyConfig',
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
        'urlManager'=>array(
//			'urlFormat'=>'path',
            'showScriptName'=>false,
			/*'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),*/
		),
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=tempo_lite',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
            'schemaCachingDuration' => 1,
            //'enableParamLogging' => true,
		),
		'errorHandler'=>array(
			// use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
		'log'=>array(
			'class'=>'CLogRouter',
            'routes'=>array(
//                array(
//                    'class'=>'CFileLogRoute',
//                    'levels'=>'error, warning',
//                ),
                array(
                    'class'=>'XWebDebugRouter',
                    'config'=>'alignLeft, opaque, runInDebug, yamlStyle',
                    'levels'=>'error, warning, trace, profile, info',
                ),
            ),
		),
        
	),

    // application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
        'hashSalt'=>'D4gQf032',
        'jui'=>array(
            'themeUrl'=>'/css/jui',
            'theme'=>'redmond',
        )
	),
);