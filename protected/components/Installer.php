<?php

// Делать установку defaultRules из подключенного поведения AccessCBehavior

class Installer extends CApplicationComponent
{
    var $ipFilters = array();

    public function installTable($className, $tableName='')
    {
        if (!$this->allowAccess()) return false;
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
                if (method_exists($className, 'install')) {
                    call_user_func(array($className, 'install'));
                }
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
    }

    public function installAll($withUnits=true)
    {
        if (!$this->allowAccess()) return false;

        foreach (Yii::app()->params['coreModels'] as $className) {

            $this->installTable($className);

            $this->installDefaultAccess($className);

        }

        if ($withUnits)
            ContentUnit::install(array_keys(ContentUnit::getAvailableUnits()));

    }

    public function installDefaultAccess($acoClass)
    {
        $sql = 'select count(*) from `' . AccessItem::tableName() . '` where `aco_class` = :aco_class';
        $alreadyInstalled = Yii::app()->getDb()->createCommand($sql)->queryScalar(array('aco_class' => $acoClass));

        $defaultRules = AccessCBehavior::getDefaultRulesByClassName($acoClass);

        if ($alreadyInstalled == 0 && !empty($defaultRules)) {

            $items = array();

            foreach ($defaultRules as $action => $params) {

                if (is_array($params)) foreach ($params as $aroClass => $datas) {

                    if (is_array($datas)) foreach ($datas as $data) {

                        $aroKey = $data[0];
                        $aroValue = $data[1];
                        $isDeny = isset($data['deny']) ? $data['deny'] : false;

                        $items[] = "('{$acoClass}', '', '', '{$aroClass}', '{$aroKey}', '{$aroValue}', '{$action}', '{$isDeny}')";

                    }
                }
            }

            $sql = 'insert into `' . AccessItem::tableName() . '` (`aco_class`, `aco_key`, `aco_value`, `aro_class`, `aro_key`, `aro_value`, `action`, `is_deny`) values ' . implode(', ', $items);
            return Yii::app()->getDb()->createCommand($sql)->execute();

        }
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

    protected function allowAccess()
    {
        return $this->allowIp(Yii::app()->request->userHostAddress);
    }

    protected function allowIp($ip)
    {
        if(empty($this->ipFilters))
            return false;
        foreach($this->ipFilters as $filter)
        {
            if($filter==='*' || $filter===$ip || (($pos=strpos($filter,'*'))!==false && !strncmp($ip,$filter,$pos)))
                return true;
        }
        return false;
    }

}