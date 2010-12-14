<?php
class Language
{
    public function configFilename()
    {
        return Yii::getPathOfAlias('config.languages').'.php';
    }

    public function loadConfig()
    {
        return include(self::configFilename());
    }

}