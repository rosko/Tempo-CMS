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
        $ret = ClassHelper::getBehaviorPropertyByClassName($className, 'AccessCBehavior', 'attributes');
        if (empty($ret)) $ret = array();
        return $ret;
    }

    public static function getDefaultRulesByClassName($className)
    {
        $ret = ClassHelper::getBehaviorPropertyByClassName($className, 'AccessCBehavior', 'defaultRules');
        if (empty($ret)) $ret = array();
        return $ret;
    }

    public function beforeFind($event)
    {
        $this->getOwner()->allowed('read');
    }

    public function beforeDelete($event)
    {
        $event->isValid = Yii::app()->user->checkAccess('delete', array('object' => $this->getOwner()));
    }

    public function beforeSave($event)
    {
        $event->isValid = Yii::app()->user->checkAccess($this->getOwner()->getIsNewRecord() ? 'create' : 'update', array('object' => $this->getOwner()));
    }

    public function allowed($action='read')
    {
        if (ClassHelper::getBehaviorPropertyByClassName(get_class($this->getOwner()), 'AccessCBehavior', 'class')) {

            $user = Yii::app()->user->data;

            if (ClassHelper::getBehaviorPropertyByClassName(get_class($user), 'AccessRBehavior', 'class')
                && !$user->checkFullAccess()) {

                $params = array(
                    'aco_class' => get_class($this->getOwner()),
                    'action' => $action,
                );

                $acoWhere = array(
                    '(a.`aco_key` = "" AND a.`aco_value` = "")',
                    '(a.`aco_key` = "id" AND a.`aco_value` = t.`id`)'
                );
                $cAttributes = AccessCBehavior::getAttributesByClassName($params['aco_class']);
                foreach ($cAttributes as $attrName) {
                    $acoWhere[] = '(a.`aco_key` = "'.$attrName.'" AND a.`aco_value` = t.`'.$attrName.'`)';
                }

                $acoWhereStatement = implode(' OR ' , $acoWhere);
                $aroWhereStatement = AccessRBehavior::generateAroWhereStatement($user, $params, 'a.');

                $this->getOwner()->getDbCriteria()->mergeWith(
                    array(
                        'join' => 'INNER JOIN `'.AccessItem::tableName().'` a
                                     ON    a.action = :action
                                       AND a.aco_class = :aco_class
                                       AND a.aro_class = :aro_class
                                       AND ('.$acoWhereStatement.')
                                       AND ('.$aroWhereStatement.')',
                        'params' => $params,
                    )
                );
            }
        }
        return $this->getOwner();
    }


}