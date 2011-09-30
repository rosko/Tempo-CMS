<?php
$local_paths = array(
    dirname(__FILE__).'/www/local',
    dirname(__FILE__).'/public_html/local',
    dirname(__FILE__).'/../www/local',
    dirname(__FILE__).'/../public_html/local',
    dirname(__FILE__).'/../../www/local',
    dirname(__FILE__).'/../../public_html/local',
);
foreach ($local_paths as $local)
    if (is_dir($local)) break;

$protected_paths = array(
    dirname(__FILE__).'/../protected',
    dirname(__FILE__).'/protected',
    $local.'/protected',
);
foreach ($protected_paths as $protected)
    if (is_dir($protected)) break;

// change the following paths if necessary
$yiit=$protected.'/yii/framework/yiit.php';
$config=$protected.'/config/main.php'; // Path to Tempo config
$local_config=$local.'/config/test.php'; // Path to local config

require_once($yiit);
require_once(dirname(__FILE__).'/WebTestCase.php');

Yii::createWebApplication($config);
