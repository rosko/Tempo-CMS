<?php

class Settings extends CApplicationComponent
{
    public $tableName = 'settings';
    public $model;

    public function init()
    {
        $this->tableName = Yii::app()->db->tablePrefix . $this->tableName;            
        $this->model = new SiteSettingsForm;
        try
        {
            $this->loadAll();
        }
        catch(Exception $e)
        {
            if (Yii::app()->db->active) {
                Yii::app()->installer->installTable('SiteSettingsForm', $this->tableName);
                $this->saveAll(SiteSettingsForm::defaults());
                Yii::app()->installer->installAll();
                $this->loadAll();
            } else
                echo Yii::t('cms', 'Error! Check configuration file "protected/config/config.php", is database setting correct. Or delete configuration file for installing system.');
        }
    }
    
    public function loadAll()
    {
        $tmp = null;
        if (Yii::app()->cache) {
            $tmp = Yii::app()->cache->get('Settings');
        }
        if (!$tmp) {
            $sql = 'SELECT * FROM `' . $this->tableName . '`';
            $command = Yii::app()->db->createCommand($sql);
            $tmp = $command->queryAll();
            Yii::app()->cache->set('Settings', $tmp);
        }
        foreach ($tmp as $s)
        {
            $s['name'] = $this->getI18nFieldName($s['name']);
            $this->model->{$s['name']} = $s['value'];
        }
    }

    public function getI18nFieldName($attr, $language='')
    {
        $className = get_class($this->model);
        if (!$language)
            $language = Yii::app()->language;
        if (in_array($attr, call_user_func(array($className, 'i18n'))))
            $attr = $language . '_' . $attr;
        return $attr;
    }

    public function saveAll($attrs)
    {
        $sql_arr = array();
        $params = array();
        foreach ($attrs as $name => $value)
        {
            $name = $this->getI18nFieldName($name);
            $this->setValue($name, $value);
        }
        Yii::app()->cache->set('Settings', null);
    }

    public function getValue($name)
    {
        $name = $this->getI18nFieldName($name);
        $value = $this->model->{$name};
        $unser = @unserialize($value);
        return $unser===FALSE ? $value : $unser;
        }
    
    public function setValue($name, $value, $save = true)
    {
        $name = $this->getI18nFieldName($name);
        if (is_array($value))
            $value = serialize($value);
        $save = $save && ($this->model->{$name} != $value);
            
        $this->model->{$name}  = $value;
        if ($save)
        {
            $sql = 'UPDATE `' . $this->tableName . '` SET `value` = :value WHERE `name` = :name';
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(':value', $value, PDO::PARAM_STR);
            $command->bindValue(':name', $name, PDO::PARAM_STR);
            $q = $command->execute();
            if (!$q) {
                $sql = 'INSERT INTO `' . $this->tableName . '` (`name`, `value`) VALUES (:name, :value)';
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(':value', $value, PDO::PARAM_STR);
                $command->bindValue(':name', $name, PDO::PARAM_STR);
                $q = $command->execute();
            }
            return $q;
        }
        return true;
    }
    
}
