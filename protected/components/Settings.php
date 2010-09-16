<?php

class Settings extends CApplicationComponent
{
    public $tableName = 'settings';
    public $model;

    public function init()
    {
        $this->model = new SiteSettingsForm;
        $this->loadAll();
    }
    
    public function loadAll()
    {
        $sql = 'SELECT * FROM `' . $this->tableName . '`';
        $command = Yii::app()->db->createCommand($sql);
        $tmp = $command->queryAll();
        foreach ($tmp as $s)
        {
            $this->model->{$s['name']} = $s['value'];
        }
    }

    public function saveAll($attrs)
    {
        $sql_arr = array();
        $params = array();
        foreach ($attrs as $name => $value)
        {
            $this->setValue($name, $value);
        }
        
    }

    public function getValue($name)
    {
        $value = $this->model->{$name};
        $unser = unserialize($value);
        return $unser===FALSE ? $value : $unser;
    }    
    
    public function setValue($name, $value, $save = true)
    {
        $save = $save && ($this->getValue($name) != $value);
        if (is_array($value)) $value = serialize($value);
        $this->model->{$name}  = $value;
        if ($save)
        {
//            if (isset($this->model->{$name}))
//            {
                $sql = 'UPDATE `' . $this->tableName . '` SET `value` = :value WHERE `name` = :name';
//            }
//            else {
//                $sql = 'INSERT INTO `' . $this->tableName . '` (`name`, `value`) VALUES (:name, :value)';
//            }
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(':value', $value, PDO::PARAM_STR);
            $command->bindValue(':name', $name, PDO::PARAM_STR);
            return $command->execute();
        }
        return true;
    }
    
}
