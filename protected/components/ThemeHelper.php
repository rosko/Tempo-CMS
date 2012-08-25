<?php

class ThemeHelper
{
    public static function loadConfig($name=null)
    {
        if (!$name) $name = Yii::app()->theme->name;
        $path = Yii::getPathOfAlias('webroot.themes.'.$name.'.theme').'.php';
        if (is_file($path))
            return include($path);
    }

    /*
     * $what = areas, colors, layouts, screenshot
     */
    public static function getDefined($what, $name=null)
    {
        $config = self::loadConfig($name);
        if ($config)
            return $config[$what];
    }

}