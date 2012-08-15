<?php

class ClassHelper
{

    public static function getBehaviorPropertyByClassName($className, $behaviorClassName, $property)
    {
        if (method_exists($className, 'behaviors')) {
            $behaviors = call_user_func(array($className, 'behaviors'));
            foreach ($behaviors as $behavior) {

                if (isset($behavior['class']) &&
                    ($behavior['class'] == $behaviorClassName ||
                        strpos($behavior['class'], $behaviorClassName) !== false)) {

                    if (isset($behavior[$property]) && is_array($behavior[$property]))
                        return $behavior[$property];
                }
            }
        }
        return array();
    }

}