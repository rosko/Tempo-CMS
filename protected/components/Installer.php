<?php

class Installer extends CApplicationComponent
{
    public function installTable($className, $tableName='')
    {
        if (!is_object($className) && !class_exists($className))
            return false;

        $options = 'ENGINE=InnoDB';

        if (!$tableName && method_exists($className, 'tableName'))
            $tableName = call_user_func(array($className, 'tableName'));
        if (method_exists($className, 'scheme') && $tableName) {
            $scheme = call_user_func(array($className, 'scheme'));
            $className::scheme();
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
                    }
                }
            }
        }
        if (method_exists($className, 'install') &&
            (Yii::app()->db->createCommand()->select('count(*)')->from($tableName)->queryScalar()==0) ) {
            call_user_func(array($className, 'install'));
        }
    }

    public function installAll()
    {
        Unit::loadTypes();
        $classNames = array_merge(
            array('Page', 'PageUnit', 'Unit', 'User'),
            array_keys(Unit::loadConfig())
        );
        foreach ($classNames as $className) {
            $this->installTable($className);
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
}