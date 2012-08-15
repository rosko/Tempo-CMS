<?php

/*
 * Поведение подключается к моделям, которые собираются получить доступ (обычно User и Role)
 */

class AccessRBehavior extends CActiveRecordBehavior
{
    public $fullAccess = array();
    public $attributes = array();

    /**
     * Проверяет наличие доступа
     *
     * @param $action идентификатор действия (create, update, read, delete etc)
     * @param $object может быть объектом, строкой (именем класса)
     * или одномерным массивом типа array($objectClassName, $objectId)
     * @return bool
     */
    public function may($action, $object)
    {
        // 1. Проверяем на наличие полного доступа (fullAccess)
        foreach ($this->fullAccess as $fullAccessItem) {

            $itemAllow = true;
            if (is_array($fullAccessItem)) {

                foreach ($fullAccessItem as $attrName => $attrValue) {

                    $thisAllow = false;

                    if (strpos($attrName, '.') !== false) {
                        $t = explode('.', $attrName);
                        $attrName = $t[0];
                        $keyName = $t[1];
                    } else {
                        $keyName = null;
                    }

                    if ($this->getOwner()->hasAttribute($attrName) ||
                        $this->getOwner()->hasProperty($attrName) ||
                        isset($this->getOwner()->getMetaData()->relations[$attrName])) {

                        $value = $this->getOwner()->{$attrName};

                        if (is_array($value)) {
                            if ($keyName) {
                                foreach ($value as $valueItem) {
                                    if (isset($valueItem[$keyName]) && $valueItem[$keyName] == $attrValue) {
                                        $thisAllow = true;
                                        break;
                                    }
                                }
                            } else {
                                $thisAllow = in_array($attrValue, $value);
                            }
                        } else {
                            if ($keyName && isset($value[$keyName])) {
                                $thisAllow = $attrValue == $value[$keyName];
                            } else {
                                $thisAllow = $attrValue == $value;
                            }
                        }

                    }

                    $itemAllow = $itemAllow && $thisAllow;

                }

                if ($itemAllow) {
                    return true;
                }
            }
        }

        // 2. Выбираем из БД все правила, которые относятся к нужному действию,
        //     а также к вопрошающему и контролируемому объектам

        $params = array('action' => $action);
        $acoWhere = array(
            '(`aco_key` = "" AND `aco_value` = "")'
        );

        if (is_object($object)) {

            $params[':aco_class'] = get_class($object);
            $acoWhere[] = '(`aco_key` = "id" AND `aco_value` = :aco_id)';
            $params['aco_id'] = $object->id;
            $cAttributes = AccessCBehavior::getAttributesByClassName($params['aco_class']);
            foreach ($cAttributes as $index => $attrName) {
                if ($object->hasAttribute($attrName) || $object->hasProperty($attrName)) {
                    $acoWhere[] = '(`aco_key` = :aco_'.$index.' AND `aco_value` = :aco_'.$attrName.')';
                    $params['aco_'.$index] = $attrName;
                    $params['aco_'.$attrName] = $object->{$attrName};
                }
            }

        } elseif (is_array($object)) {

            $params['aco_class'] = $object[0];
            $acoWhere[] = '(`aco_key` = "id" AND `aco_value` = :aco_id)';
            $params['aco_id'] = $object[1];

        } else {

            $params['aco_class'] = $object;

        }

        $aroWhere = array(
            '(`aro_key` = "" AND `aro_value` = "")',
            '(`aro_key` = "id" AND `aro_value` = :aro_id)',
        );

        $params['aro_class'] = get_class($this->getOwner());
        $params['aro_id'] = $this->getOwner()->id;
        $rAttributes = self::getAttributesByClassName($params['aro_class']);
        foreach ($rAttributes as $index => $attrName) {
            if ($this->getOwner()->hasAttribute($attrName) || $this->getOwner()->hasProperty($attrName)) {

                $attrValue = $this->getOwner()->{$attrName};

                if (is_array($attrValue)) {

                    foreach ($attrValue as $attrValueItemKey => $attrValueItemValue) {

                        $aroWhere[] = '(`aro_key` = :aro_'.$index.'_'.$attrValueItemKey.' AND `aro_value` = :aro_'.$attrName.'_'.$attrValueItemKey.')';
                        $params['aro_'.$index.'_'.$attrValueItemKey] = $attrName;
                        $params['aro_'.$attrName.'_'.$attrValueItemKey] = $attrValueItemValue;

                    }

                } else {

                    $aroWhere[] = '(`aro_key` = :aro_'.$index.' AND `aro_value` = :aro_'.$attrName.')';
                    $params['aro_'.$index] = $attrName;
                    $params['aro_'.$attrName] = $attrValue;

                }

            }
        }

        $sql = 'SELECT * FROM`' . AccessItem::tableName() . '`
                WHERE `action` = :action
                  AND `aco_class` = :aco_class
                  AND `aro_class` = :aro_class
                  AND ( ' . implode(' OR ' , $acoWhere). ' )
                  AND ( ' . implode(' OR ' , $aroWhere). ' )
                ORDER BY `is_deny` DESC';

        $accessItems = Yii::app()->db->createCommand($sql)->bindValues($params)->queryAll();

        // 3. Просматриваем все правила и определяем результат
        if (count($accessItems) == 0) return false;

        foreach ($accessItems as $accessItem) {
            if ($accessItem['is_deny']) return false;
        }

        return true;
    }

    public static function getAttributesByClassName($className)
    {
        return ClassHelper::getBehaviorPropertyByClassName($className, 'AccessRBehavior', 'attributes');
    }

}