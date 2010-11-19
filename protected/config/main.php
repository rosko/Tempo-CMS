<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'language'=>'ru', // Перенести настройку в БД, когда будем делать мультиязычность
    'theme'=>'classic', // Перенести настройку в БД, когда будем делать поддержку переключения графических тем
    'defaultController' => 'page',

//	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.units.*',
		'application.components.inputs.*',
        'application.extensions.yiidebugtb.*',
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
            'schemaCachingDuration' => 3600,
            //'enableParamLogging' => true,
		),
		'errorHandler'=>array(
			// use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
		'log'=>array(
			'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'error, warning',
                ),
//                array(
//                    'class'=>'XWebDebugRouter',
//                    'config'=>'alignLeft, opaque, runInDebug, yamlStyle',
//                    'levels'=>'error, warning, trace, profile, info',
//                ),
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