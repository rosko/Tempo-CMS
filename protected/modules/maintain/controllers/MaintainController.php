<?php

Yii::import('application.modules.maintain.components.MaintainHelper');

class MaintainController extends Controller
{
	public function filters()
	{
		return array(
			'accessControl',
		);
	}

	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array(
                    'index', ''
                ),
				'users'=>array('*'),
			),
			array('allow',
				'actions'=>array(
                ),
				'users'=>array('admin'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

    public function actionIndex()
    {
        $this->render('index');        
    }

    // Установка

    /**
     * 1. Проверка требований установщика к хостингу
     */
    public function actionCheckRequirements()
    {
        $requirements=array(
            array(
                Yii::t('MaintainModule.requirements','PHP version'),
                true,
                version_compare(PHP_VERSION,"5.1.0",">="),
                '<a href="http://www.yiiframework.com">Yii Framework</a>',
                Yii::t('MaintainModule.requirements','PHP 5.1.0 or higher is required.')),
            array(
                Yii::t('MaintainModule.requirements','$_SERVER variable'),
                true,
                ($message=MaintainHelper::checkServerVar()) === '',
                '<a href="http://www.yiiframework.com">Yii Framework</a>',
                $message),
            array(
                Yii::t('MaintainModule.requirements','Reflection extension'),
                true,
                class_exists('Reflection',false),
                '<a href="http://www.yiiframework.com">Yii Framework</a>',
                ''),
            array(
                Yii::t('MaintainModule.requirements','PCRE extension'),
                true,
                extension_loaded("pcre"),
                '<a href="http://www.yiiframework.com">Yii Framework</a>',
                ''),
            array(
                Yii::t('MaintainModule.requirements','SPL extension'),
                true,
                extension_loaded("SPL"),
                '<a href="http://www.yiiframework.com">Yii Framework</a>',
                ''),
            array(
                Yii::t('MaintainModule.requirements','DOM extension'),
                false,
                class_exists("DOMDocument",false),
                '<a href="http://www.yiiframework.com/doc/api/CWsdlGenerator">CWsdlGenerator</a>',
                ''),
            array(
                Yii::t('MaintainModule.requirements','PDO extension'),
                false,
                extension_loaded('pdo'),
                Yii::t('MaintainModule.requirements','All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>'),
                ''),
            array(
                Yii::t('MaintainModule.requirements','PDO SQLite extension'),
                false,
                extension_loaded('pdo_sqlite'),
                Yii::t('MaintainModule.requirements','All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>'),
                Yii::t('MaintainModule.requirements','This is required if you are using SQLite database.')),
            array(
                Yii::t('MaintainModule.requirements','PDO MySQL extension'),
                false,
                extension_loaded('pdo_mysql'),
                Yii::t('MaintainModule.requirements','All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>'),
                Yii::t('MaintainModule.requirements','This is required if you are using MySQL database.')),
            array(
                Yii::t('MaintainModule.requirements','PDO PostgreSQL extension'),
                false,
                extension_loaded('pdo_pgsql'),
                Yii::t('MaintainModule.requirements','All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>'),
                Yii::t('MaintainModule.requirements','This is required if you are using PostgreSQL database.')),
            array(
                Yii::t('MaintainModule.requirements','Memcache extension'),
                false,
                extension_loaded("memcache"),
                '<a href="http://www.yiiframework.com/doc/api/CMemCache">CMemCache</a>',
                ''),
            array(
                Yii::t('MaintainModule.requirements','APC extension'),
                false,
                extension_loaded("apc"),
                '<a href="http://www.yiiframework.com/doc/api/CApcCache">CApcCache</a>',
                ''),
            array(
                Yii::t('MaintainModule.requirements','Mcrypt extension'),
                false,
                extension_loaded("mcrypt"),
                '<a href="http://www.yiiframework.com/doc/api/CSecurityManager">CSecurityManager</a>',
                Yii::t('MaintainModule.requirements','This is required by encrypt and decrypt methods.')),
            array(
                Yii::t('MaintainModule.requirements','SOAP extension'),
                false,
                extension_loaded("soap"),
                '<a href="http://www.yiiframework.com/doc/api/CWebService">CWebService</a>, <a href="http://www.yiiframework.com/doc/api/CWebServiceAction">CWebServiceAction</a>',
                ''),
            array(
                Yii::t('MaintainModule.requirements','GD extension with<br />FreeType support'),
                false,
                ($message=MaintainHelper::checkGD()) === '',
                //extension_loaded('gd'),
                '<a href="http://www.yiiframework.com/doc/api/CCaptchaAction">CCaptchaAction</a>',
                $message),
        );

        $result=1;  // 1: all pass, 0: fail, -1: pass with warnings

        foreach($requirements as $i=>$requirement)
        {
            if($requirement[1] && !$requirement[2])
                $result=0;
            else if($result > 0 && !$requirement[1] && !$requirement[2])
                $result=-1;
            if($requirement[4] === '')
                $requirements[$i][4]='&nbsp;';
        }

        $filename = Yii::getPathOfAlias('application.modules.maintain.assets.requirements') . '.css';
        $baseUrl = Yii::app()->getAssetManager()->publish($filename);
        Yii::app()->getClientScript()->registerCssFile($baseUrl);

        $this->render('requirements',array(
            'requirements'=>$requirements,
            'result'=>$result,
            'serverInfo'=>MaintainHelper::getServerInfo()));
    }

    /**
     * 2. Ввод и проверка ключа
     */
    public function actionCheckLicenseKey()
    {

    }

    /**
     * 3. Если надо, скачивание или обновление файлов.
     */
    public function actionDownloadFiles()
    {

    }

    /**
     * 4. Ввод параметров БД. Ввод некоторых исходных параметров:
     * - название сайта
     * - логин и пароль администратора
     * - и т.д.
     */
    public function actionInputParams()
    {

    }

    /**
     * 5. Установка
     * = собственно система
     * - загрузка дампа sql (с create)
     * - операции с файлами (установка нужных прав доступа)
     *
     * = каждый модуль/юнит
     * - загрузка дампа sql (с create)
     * - операции с файлами (установка нужных прав доступа)
     */
    public function actionInstall()
    {
        
    }

    /**
     * 6. Поздравление.
     */
    public function actionDone()
    {
        
    }

    // Обновление

    /**
     * 1. Проверка установлена ли система
     */
    public function actionCheckInstall()
    {
        
    }

    /*
     * 2. Проверка ключа
     * actionCheckLicenseKey()
     */

    /**
     * 3. Проверка целостности файлов.
     * Если требуется перезаписать файлы, которые были отредактированы
     * вручную, то выдать предупреждение.
     */
    public function actionCheckIntegrity()
    {

    }

    /*
     * 4. Скачивание файлов.
     * actionDownloadFiles()
     */

    /*
     * 5. Проверка требований cms к хостингу
     * actionCheckRequirements()
     */

    /**
     * 6. Обновление
     * = собственно система
     * - загрузка дампа sql (с alter)
     * - операции с файлами (установка нужных прав доступа)
     *
     * = каждый модуль/юнит
     * - загрузка дампа sql (с alter)
     * - операции с файлами (установка нужных прав доступа)
     */
    public function actionUpdate()
    {

    }

    /**
     * 7. Поздравление.
     * actionDone()
     */

}
