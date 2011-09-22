<?php
// Нужно сделать блокирующий доступ (чтобы во время установки сайт стал недоступен для всех остальных)
// Тогда после каждого шага информация записывается в файл
// Если инсталляция оборвана, то восстановление работы сайты происходит в течении какого-то времени
// (например, 5 минут после последнего обновления файла с информацией об инсталляции)
// При этом инсталляцию можно всегда восстановить на том шаге, где закончили в прошлый раз
// Блокировка доступа начинается после первого шага
// в файл записывается: логин пользователя, ай-пи, дата и время последнего обновления файла, название текущего шага, все данные введенные пользователем
// Можно вернуться к любому предыдущему шагу и что-либо исправить

class InstallModule extends CWebModule
{
    public $defaultController = 'install';
    public $blockFile = 'installInfo.serialize';
    public $config = array();

    public function config()
    {
        if (!empty($config)) return $config;
        
        return array(
            'title' => 'Title',
            'logo' => '/logo.png',

            // silent = выводит сообщения только если нужно что-то ввести или есть какая-то ошибка, например, не выводит сообщение, что все требования к хостингу удовлетворены
            // verbose = вывод сообщение по каждой из команд
            'mode' => 'verbose',

            'steps' => array(
                // Шаг 1
                array(
                    'requirements',
                    'requirementsRules'=>array(
                        array('php', 'min'=>'5.1.0'),
                        array('mysql', 'min'=>'5'),
                        array('serverVar'),
                        array('phpClass', 'name'=>'Reflection'),
                        array('extension', 'name'=>'pcre'),
                        array('extension', 'name'=>'SPL'),
                        array('phpClass', 'name'=>'DOMDocument', 'canSkip'=>true),
                        array('extension', 'name'=>'pdo', 'canSkip'=>true),
                        array('extension', 'name'=>'pdo_mysql', 'canSkip'=>true, 'message'=>'This is required if you are using MySQL database.'),
                        array('extension', 'name'=>'memcache', 'canSkip'=>true),
                        array('extension', 'name'=>'apc', 'canSkip'=>true),
                        array('extension', 'name'=>'mcrypt', 'message'=>'This is required by encrypt and decrypt methods.'),
                        array('extension', 'name'=>'soap'),
                        array('gd', 'needFreeType'=>true),
                    ),
                ),
                array(
                    'checkDB',
                    'forceDbCreate'=>true,
                ),
            ),

            'wizard' => array(
                // Шаг 1
                array(
                    array('text',
                        'file'=>'requirements',
                        'alwaysShow'=>true,
                    ),
                    array('required', 'folder', 'is'=>'readable', 'name'=>Yii::app()->getRuntimePath()),
                    array('required', 'php', 'min'=>'5.1.0'),
                    array('required', 'mysql', 'min'=>'5'),
                    array('required', 'serverVar'),
                    array('required', 'phpClass', 'name'=>'Reflection'),
                    array('required', 'extension', 'name'=>'pcre'),
                    array('required', 'extension', 'name'=>'SPL'),
                    array('required', 'phpClass', 'name'=>'DOMDocument', 'canSkip'=>true),
                    array('required', 'extension', 'name'=>'pdo', 'canSkip'=>true),
                    array('required', 'extension', 'name'=>'pdo_mysql', 'canSkip'=>true, 'message'=>'This is required if you are using MySQL database.'),
                    array('required', 'extension', 'name'=>'memcache', 'canSkip'=>true),
                    array('required', 'extension', 'name'=>'apc', 'canSkip'=>true),
                    array('required', 'extension', 'name'=>'mcrypt', 'message'=>'This is required by encrypt and decrypt methods.'),
                    array('required', 'extension', 'name'=>'soap'),
                    array('required', 'gd', 'needFreeType'=>true),
                ),
                // Шаг 2
                array(
                    array('text', 'title'=>'Database settings'),
                    array('input', 'text', 'name'=>'db_hostname', 'label'=>'Hostname', 'value'=>'localhost'),
                    array('input', 'text', 'name'=>'db_port', 'label'=>'Port', 'value'=>'3306'),
                    array('input', 'text', 'name'=>'db_basename', 'label'=>'Basename'),
                    array('input', 'text', 'name'=>'db_prefix', 'label'=>'Prefix'),
                    array('input', 'text', 'name'=>'db_username', 'label'=>'Username'),
                    array('input', 'password', 'name'=>'db_password', 'label'=>'Password'),
                    array('input', 'checkbox', 'name'=>'db_create', 'label'=>'Create database'),
                ),
                // Шаг 3
                array(
                    array('checkDB', 'mysql',
                        'title'=>'Check the database connection',
                        'hostname'=>'db_hostname',
                        'port'=>'db_port',
                        'username'=>'db_username',
                        'password'=>'db_password',
                        'basename'=>'db_basename',
                        'prefix'=>'db_prefix',
                        'create'=>'db_create',
                        //'needEmpty'=>true,
                    ),
                    array('required', 'folder', 'is'=>'readable', 'alias'=>'application.config'),
                    array('required', 'file', 'is'=>'writable', 'alias'=>'application.config', 'name'=>'/config.php', 'canSkip'=>true),
                    array('text', 'title'=>'Administrator settings'),
                    array('input', 'text', 'name'=>'admin_login', 'label'=>'Admin login'),
                    array('input', 'text', 'name'=>'admin_email', 'label'=>'Admin e-mail'),
                    array('input', 'password', 'name'=>'admin_password', 'label'=>'Admin password', 'repeat'=>true),
                ),
                array(
                    array('installFrom', 'remoteServer', 'url'=>'http://tempo/?r=install', 'ftpOnly'=>false),
                ),
            ),
        );
    }

}