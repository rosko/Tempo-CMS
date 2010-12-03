<?php

class MaintainHelper
{
    function checkServerVar()
    {
        $vars=array('HTTP_HOST','SERVER_NAME','SERVER_PORT','SCRIPT_NAME','SCRIPT_FILENAME','PHP_SELF','HTTP_ACCEPT','HTTP_USER_AGENT');
        $missing=array();
        foreach($vars as $var)
        {
            if(!isset($_SERVER[$var]))
                $missing[]=$var;
        }
        if(!empty($missing))
            return Yii::t('MaintainModule.requirements','$_SERVER does not have {vars}.',array('{vars}'=>implode(', ',$missing)));

        if(realpath($_SERVER["SCRIPT_FILENAME"]) !== Yii::getPathOfAlias('webroot.index').'.php')
            return Yii::t('MaintainModule.requirements','$_SERVER["SCRIPT_FILENAME"] must be the same as the entry script file path.');

        if(!isset($_SERVER["REQUEST_URI"]) && isset($_SERVER["QUERY_STRING"]))
            return Yii::t('MaintainModule.requirements','Either $_SERVER["REQUEST_URI"] or $_SERVER["QUERY_STRING"] must exist.');

        if(!isset($_SERVER["PATH_INFO"]) && strpos($_SERVER["PHP_SELF"],$_SERVER["SCRIPT_NAME"]) !== 0)
            return Yii::t('MaintainModule.requirements','Unable to determine URL path info. Please make sure $_SERVER["PATH_INFO"] (or $_SERVER["PHP_SELF"] and $_SERVER["SCRIPT_NAME"]) contains proper value.');

        return '';
    }

    function checkGD()
    {
        if(extension_loaded('gd'))
        {
            $gdinfo=gd_info();
            if($gdinfo['FreeType Support'])
                return '';
            return Yii::t('MaintainModule.requirements','GD installed<br />FreeType support not installed');
        }
        return Yii::t('MaintainModule.requirements','GD not installed');
    }

    function getServerInfo()
    {
        $info[]=isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
        $info[]=@strftime('%Y-%m-%d %H:%M',time());

        return implode(' ',$info);
    }
}
