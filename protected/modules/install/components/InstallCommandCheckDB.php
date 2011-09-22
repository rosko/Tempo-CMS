<?php

class InstallCommandCheckDB
{
    public static function mysql($params)
    {
        $vars = array('hostname', 'port', 'username', 'password', 'basename', 'prefix', 'create');
        foreach ($vars as $var) {
            if (!empty($params[$var]) && isset($_POST[$params[$var]]))
                $$var = $_POST[$params[$var]];
        }
        $db = Yii::createComponent(array(
            'class'=>'CDbConnection',
			'connectionString' => 'mysql:host='.$hostname.';port='.$port.';dbname='.$basename,
			'username' => $username,
			'password' => $password,
            'tablePrefix' => $prefix,
			'charset' => 'utf8',
        ));
        $params['status'] = true;
        try
        {
            $db->setActive(true);
        } catch (CDbException $e) {
            if (!empty($create)) {
                $db = Yii::createComponent(array(
                    'class'=>'CDbConnection',
                    'connectionString' => 'mysql:host='.$hostname.';port='.$port,
                    'username' => $username,
                    'password' => $password,
                    'tablePrefix' => $prefix,
                    'charset' => 'utf8',
                ));
                try
                {
                    $db->createCommand('create database `'.$basename.'`')->execute();
                } catch (CDbException $e) {
                    $params['status'] = false;
                    $params['message'] = $e->getMessage();
                }
            } else {
                    $params['status'] = false;
                    $params['message'] = $e->getMessage();
            }
        }
        if ($params['status'] && !empty($params['needEmpty'])) {

            if (count($db->createCommand('show tables' . ($prefix?'':' like "'.$prefix.'%"'))->queryColumn())) {
                $params['status'] = false;
                $params['message'] = 'Database is not empty';
            }
        }
        return $params;
        
    }
}