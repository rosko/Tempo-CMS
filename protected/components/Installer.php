<?php

class Installer extends CApplicationComponent
{
    public function installTable($className, $tableName='')
    {
        if (!is_object($className) && !class_exists($className))
            return false;

        $options = ' ENGINE=InnoDB  DEFAULT CHARSET=utf8 ';

        if (!$tableName && method_exists($className, 'tableName'))
            $tableName = call_user_func(array($className, 'tableName'));

        Yii::app()->getCache()->delete('yii:dbschema'.Yii::app()->getDb()->connectionString.':'.Yii::app()->getDb()->username.':'.$tableName);

        if (method_exists($className, 'scheme') && $tableName) {
            $scheme = call_user_func(array($className, 'scheme'));
            if (method_exists($className, 'baseScheme') && $tableName) {
                $scheme = CMap::mergeArray(call_user_func(array($className, 'baseScheme')), $scheme);
            }
            if (method_exists($className, 'i18n')) {
                $i18n_columns = call_user_func(array($className, 'i18n'));
                $langs = array_keys(I18nActiveRecord::getLangs());
                $columns = array();
                foreach ($scheme  as $k => $v) {
                    if (in_array($k, $i18n_columns)) {
                        foreach ($langs as $lang) {
                            $columns[$lang.'_'.$k] = $v;
                        }
                    } else {
                        $columns[$k] = $v;
                    }
                }
            } else {
                $columns = $scheme;
            }
            if (!Yii::app()->db->createCommand('show tables like "'.$tableName.'"')->queryColumn()) {
                Yii::app()->db->createCommand()->createTable($tableName, $columns, $options);
            } elseif (method_exists($className, 'getTableSchema')) {
                $tableSchema = call_user_func(array($className, 'model'))->getTableSchema();
                $simpleScheme = self::getSimpleScheme($tableSchema);
                $add_columns = array_diff_assoc($columns, $simpleScheme);
                foreach ($add_columns as $k => $v) {
                    if (isset($simpleScheme[$k])) {
                        Yii::app()->db->createCommand()->alterColumn($tableName, $k, $v);
                    } else {
                        Yii::app()->db->createCommand()->addColumn($tableName, $k, $v);
                        if (isset($langs) && is_array($langs)) {
                            $underscorePos = strpos($k, '_');
                            if ($underscorePos !== false) {
                                $prefix = substr($k,0,$underscorePos);
                                $base = substr($k, $underscorePos+1);
                                if (isset($simpleScheme[$base]) && in_array($prefix, $langs)) {
                                    Yii::app()->getDb()->createCommand('update `'.$tableName.'` set `'.$k.'` = `'.$base.'`')->execute();
                                }
                            }
                        }
                    }
                }
            }
        }
        if (method_exists($className, 'install') &&
            (Yii::app()->db->createCommand()->select('count(*)')->from($tableName)->queryScalar()==0) ) {
            call_user_func(array($className, 'install'));
        }
    }

    public function installAll($withUnits=true)
    {
        // 'ARRights', 'AuthItem', 'AuthItemChild', 'AuthAssignment'
        $classNames = array('User', 'Page', 'PageUnit', 'Unit');
        foreach ($classNames as $className)
            $this->installTable($className);
        $this->installAuth();
        if ($withUnits)
            ContentUnit::install(array_keys(ContentUnit::getAvailableUnits()));

    }

    protected static function getSimpleScheme($tableSchema) {
        $ret = array();
        $types = array(
            'text' => 'text',
            'float' => 'float',
            'datetime' => 'datetime',
            'timestamp' => 'timestamp',
            'time' => 'time',
            'date' => 'date',
            'blob' => 'binary',
            'tinyint(1)' => 'boolean',
            'char(32)' => 'char(32)',
            'char(64)' => 'char(64)',
        );

        foreach ($tableSchema->columns as $column) {
            $type = $column->type;
            $column->dbType = strtolower($column->dbType);
            if ($column->type == 'integer') {
                if ($column->precision==1)
                    $type = 'boolean';
                elseif ($column->isPrimaryKey)
                    $type = 'pk';
            } elseif (strpos($column->dbType, 'decimal(')===0) {
                $type = 'decimal';
            } elseif (strpos($column->dbType, 'int(')===0) {
                $type = 'integer';
            } elseif (isset($types[$column->dbType])) {
                $type = $types[$column->dbType];
            }
            if (strpos($column->dbType, 'unsigned')!==false) {
                $type .= ' unsigned';
            }
            if ($column->isPrimaryKey)
                $type = 'pk';
            $ret[$column->name] = $type;
        }
        return $ret;
    }

    public function installAuth()
    {
        $auth=Yii::app()->authManager;
        if (count($auth->getAuthItems()) > 0) {
            return false;
        }

        if (method_exists($auth, 'clearAll'))
            $auth->clearAll();
        $auth->createRole('anybody', 'Anybody');

        $bizRule='return Yii::app()->user->isGuest;';
        $role = $auth->createRole('guest', 'Guest', $bizRule);
        $role->addChild('anybody');

        $bizRule='return !Yii::app()->user->isGuest;';
        $role = $auth->createRole('authenticated', 'Authenticated user', $bizRule);
        $role->addChild('anybody');

        $role = $auth->createRole('author', 'Author');
        $role->addChild('authenticated');

        $role = $auth->createRole('editor', 'Editor');
        $role->addChild('authenticated');

        $role = $auth->createRole('administrator', 'Administrator');
        $role->addChild('authenticated');

        $auth->assign('administrator', User::getAdmin()->id);

        $classes = array('Page', 'Settings', 'Unit', 'User');
        foreach ($classes as $className) {
            if (method_exists($className, 'operations')) {
                $a = call_user_func(array($className, 'operations'));
                foreach ($a as $operation => $params) {
                    $auth->createOperation($operation.$className, $params['label'], isset($params['bizRule']) ? $params['bizRule'] : null);
                    if (is_array($params['defaultRoles']))
                        foreach ($params['defaultRoles'] as $role) {
                            $auth->addItemChild($role, $operation.$className);
                        }
                }
            }
            if (method_exists($className, 'tasks')) {
                $a = call_user_func(array($className, 'tasks'));
                foreach ($a as $task => $params) {
                    $auth->createTask($task.$className, $params['label'], isset($params['bizRule']) ? $params['bizRule'] : null);
                    if (is_array($params['children']))
                        foreach ($params['children'] as $operation) {
                            $auth->addItemChild($task.$className, $operation);
                        }
                    if (is_array($params['defaultRoles']))
                        foreach ($params['defaultRoles'] as $role) {
                            $auth->addItemChild($role, $task.$className);
                        }
                }
            }
        }
        if (method_exists($auth, 'save'))
            $auth->save();
        return true;

    }
}