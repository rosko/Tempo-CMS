<?php

/*
 * Поведение подключается к моделям к которым нужно получить досутп
 */

class AccessCBehavior extends CActiveRecordBehavior
{
    // Возможные операции над сущностью
    public $operations = array();
    // Правила доступа, которые будут загружены при установке системы
    public $defaultRules = array();
    // Контролируемые поля в дополенение к id
    public $attributes = array();

    public static function getAttributesByClassName($className)
    {
        return ClassHelper::getBehaviorPropertyByClassName($className, 'AccessCBehavior', 'attributes');
    }

    public static function getDefaultRulesByClassName($className)
    {
        return ClassHelper::getBehaviorPropertyByClassName($className, 'AccessCBehavior', 'defaultRules');
    }

}