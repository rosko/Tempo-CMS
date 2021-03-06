<?php

class ContentUnit extends CComponent
{
    // Возвращает название юнита на нужном языке
    public function name($language=null)    
    {
        return false;
    }

    // Возвращает ссылку на иконку юнита
    public function icon()
    {
        return false;
    }
    
    // Версия в виде n.YYYYMMDD[HH[MM]] - последние части (часы, минуты) - необязательно
    public function version()
    {
        return false;
    }
    
    // Список виджетов имеющихся в юните
    public function widgets()
    {
        return array();
    }
    
    // Список моделей имеющихся в юните
    public function models()
    {
        return array();
    }

    // Описание функций smarty
    public function tags()
    {
        return array();
    }
    
    // Описание зависимостей этого юнита от других юнитов, файлов и версий системы
    public function dependencies()
    {
        return array();
    }
 
    
    public static function configFilename()
    {
        return Yii::getPathOfAlias('config.units').'.php';
    }
    
    public static function classMapFilename()
    {
        return Yii::getPathOfAlias('config.classmap').'.php';        
    }

    public static function loadConfig()
    {
        return include(self::configFilename());
    }

    // Настройки всего типа юнитов
    public function settings($className)
    {
        return array(
            'template' => array(
                'type'=>'TemplateSelect',
                'className'=>$className,
                'label'=>Yii::t('cms', 'Template'),
            ),
        );
    }

    public function settingsRules($className)
    {
        return array(
            array('template', 'safe'),
        );
    }

    /**
     * Получает список всех доступных юнитов (в т.ч. и не инсталлированных)
     * 
     * @return array  
     */
    public static function getAvailableUnits()
    {
        $installed = array_keys(self::loadConfig());

        $aliases = self::unitsDirsAliases();
        $tmp = array();
        $u = array();

        // Пройдемся по каждой директории, где могут быть юниты
        foreach ($aliases as $alias) {

            // В каждой директории находим директории юнитов
            $basedir = Yii::getPathOfAlias($alias);
            $handle=opendir($basedir);
            while(($file=readdir($handle))!==false)
            {
                if($file==='.' || $file==='..')
                    continue;
                $dir=$basedir.DIRECTORY_SEPARATOR.$file;
                if (is_dir($dir)) {
                    // Получаем имя класс юнита
                    $className = 'Unit'.ucfirst(basename($dir));
                    $path = $dir.DIRECTORY_SEPARATOR.$className.'.php';
                    if (is_file($path)) {
                        require_once($path);
                        $u[$className] = call_user_func(array($className, 'name'));
                        // Заполняем временный массив
                        $tmp[$className] = array(
                            'name' => $u[$className],
                            'dir_alias' => $alias,
                            'icon' => call_user_func(array($className, 'icon')),
                            'installed' => in_array($className, $installed),
                        );
                    }
                }
            }
            closedir($handle);
        }
        // Сортировк по в алмафитном порядке по названиямм юнитов
        asort($u);
        $units = array();
        // Переносим информацию из временного массива в тот, который будет возращаться
        foreach ($u as $className => $name) {
            $units[$className] = $tmp[$className];
        }
        return $units;
    }

    /**
     * Инсталлирует указаные юниты
     * 
     * @param array $classNames список классов юнитов
     */
    public static function install($classNames=null)
    {
        if (empty($classNames)) return false;
        $config = self::loadConfig();
        if (!is_array($classNames)) {
            $classNames = array($classNames);
        }
        self::loadUnits(true);
        $units = self::getAvailableUnits();
        foreach ($classNames as $className) {

            if (method_exists($className, 'models')) {
                $models = call_user_func(array($className, 'models'));
                foreach ($models as $modelClassName) {
                    Yii::app()->installer->installTable($modelClassName);
                    Yii::app()->installer->installDefaultAccess($modelClassName);
                }
            }
            $config[$className] = $units[$className]['dir_alias'];
        }
        self::saveConfig($config);
    }

    /**
     * Отключает указанные юниты
     * 
     * @param array $classNames список классов юнитов
     */
    public static function uninstall($classNames)
    {
        $config = self::loadConfig();
        if (!is_array($classNames)) {
            $classNames = array($classNames);
        }
        if (empty($classNames)) return false;
        foreach ($classNames as $className) {
            if (isset($config[$className]))
                unset($config[$className]);
        }
        self::saveConfig($config);
    }

    /**
     * Сохраняет массив установленных блоков 
     *
     * @param array $config массив установленных блоков
     */
    public static function saveConfig($config)
    {
        if (is_array($config) && !empty($config)) {
            $contents = "<?php\nreturn array(\n";
            foreach ($config as $className => $dirAlias) {
                $contents .= "\t'{$className}' => '{$dirAlias}',\n";
            }
            $contents .= ");\n";
            file_put_contents(self::configFilename(), $contents);
            
            $classmap = array();
            foreach ($config as $unitClassName => $dirAlias) {
                $dir = strtolower(substr($unitClassName,4));
                Yii::$classMap[$unitClassName] = $classmap[$unitClassName] = Yii::getPathOfAlias($dirAlias.'.'.$dir.'.'.$unitClassName).'.php';
                if (method_exists($unitClassName, 'models') && method_exists($unitClassName, 'widgets')) {
                    $classes = array_merge(
                        call_user_func(array($unitClassName, 'models')) ,
                        call_user_func(array($unitClassName, 'widgets'))
                    );
                    foreach ($classes as $className => $alias) {
                        if (is_int($className)) {
                            $className = $alias;
                            $alias = $dirAlias . '.' . $dir . '.' . $className;
                        }
                        $classmap[$className] = Yii::getPathOfAlias($alias).'.php';
                    }
                }
            }
            if (!empty($classmap)) {
                $contents = "<?php\nreturn array(\n";
                foreach ($classmap as $className => $path) {
                    $contents .= "\t'{$className}' => '{$path}',\n";
                }
                $contents .= ");\n";
                file_put_contents(self::classMapFilename(), $contents);
            }
        }
    }

    /**
     * Возвращает список юнитов, установленных в CMS
     *
     * @param bool $withNames формат возвращаемого массива
     * (true - возвращается ассоциативный массив, где ключ - имя класса юнита, 
     * а значение - название на текущем языке; 
     * false - возвращается просто список классов установленных юнитов)
     * @return array список юнитов, установленных в CMS 
     */
    public static function getInstalledUnits($withNames=false)
	{
        self::loadUnits();
        $units = array();
        $classNames = array_keys(self::loadConfig());
		foreach ($classNames as $className) {
            if (is_subclass_of($className, 'ContentUnit')) {
                $units[$className] = call_user_func(array($className, 'name'));
            }
		}
        asort($units);
        if (!$withNames) {
            $units = array_keys($units);
        }
        return $units;
	}

    public static function loadUnits($all=false)
    {
        $config = include(self::classMapFilename());
        foreach ($config as $className => $path) {
            Yii::$classMap[$className] = $path;
        }
        // Подгружаем неисталированные юниты
        if ($all) {
            $units = self::getAvailableUnits();
            foreach ($units as $unitClassName => $unit) {
                if (!$unit['installed']) {
                    $dirAlias = $unit['dir_alias'];
                    $dir = strtolower(substr($unitClassName,4));
                    Yii::$classMap[$unitClassName] = Yii::getPathOfAlias($dirAlias.'.'.$dir.'.'.$unitClassName).'.php';
                    if (method_exists($unitClassName, 'models') && method_exists($unitClassName, 'widgets')) {
                        $classes = array_merge(
                            call_user_func(array($unitClassName, 'models')) ,
                            call_user_func(array($unitClassName, 'widgets'))
                        );
                        foreach ($classes as $className => $alias) {
                            if (is_int($className)) {
                                $className = $alias;
                                $alias = $dirAlias . '.' . $dir . '.' . $className;
                            }
                            Yii::$classMap[$className] = Yii::getPathOfAlias($alias).'.php';
                        }
                    }
                }
            }
        }
    }

    public function getTemplates($className='', $templateType='')
    {
        if ($className == '')
            $className = get_class($this);

		if((Yii::app()->getViewRenderer())!==null)
			$extension=Yii::app()->getViewRenderer()->fileExtension;
		else
			$extension='.php';

        $files = array();
        $pathes = ContentModel::getTemplateDirAliases($className);
        foreach ($pathes as $path) {
            $path = Yii::getPathOfAlias($path);
            if (is_dir($path))
                $files = array_merge($files, CFileHelper::findFiles($path, array(
                    'fileTypes' => array(substr($extension,1)),
                    'level' => 0,
                    'exclude' => array(
                        $className . $extension,
                     ),
                )));
            }
        $data = array();
        foreach ($files as $file)
        {
            if ($templateType) {
                if (substr(basename($file, $extension),0,strlen($templateType)).'-' != $templateType.'-') continue;
                $data[substr(basename($file, $extension),strlen($templateType)+1)] = $file;
            } else {
                $data[basename($file, $extension)] = $file;
            }
        }
        return $data;
    }

    public function unitsDirsAliases()
    {
        return array(
            'application.units',
            'local.units'
        );
    }

}