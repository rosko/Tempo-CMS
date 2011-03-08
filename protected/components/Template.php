<?php

class Template
{
    public function loadConfig($name)
    {
        return include(Yii::getPathOfAlias('webroot.themes.'.$name.'.theme').'.php');
    }

}