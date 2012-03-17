<?php
defined('YII_DEBUG') or define('YII_DEBUG',true);

if (defined('YII_DEBUG') && YII_DEBUG) {
    ini_set('display_errors', true);
    error_reporting(E_ALL);    
}

defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

$local = '';
$local_paths = array(
    dirname(__FILE__).'/local',
    dirname(__FILE__).'/../local',
);
foreach ($local_paths as $local)
    if (is_dir($local)) break;

$protected = '';
$protected_paths = array(
    dirname(__FILE__).'/../protected',
    dirname(__FILE__).'/protected',
    $local.'/protected',
);
foreach ($protected_paths as $protected)
    if (is_dir($protected)) break;

$yii=$protected.'/yii/framework/yii.php'; // Path to Yii
$config=$protected.'/config/main.php'; // Path to Tempo config
$local_config=$local.'/config/general.php'; // Path to local config

require_once($yii);
Yii::createWebApplication($config)->run();
