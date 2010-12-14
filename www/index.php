<?php

defined('YII_DEBUG') or define('YII_DEBUG',true);

defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

$yii=dirname(__FILE__).'/../yii/framework/yii.php'; // Path to Yii
$local=dirname(__FILE__).'/local/config/general.php'; // Path to local config
$config=dirname(__FILE__).'/../protected/config/main.php'; // Path to Tempo config

require_once($yii);
Yii::createWebApplication($config)->run();
