<?php
class InstallCommandInput
{
    public static function field($params)
    {
        $params['status'] = true;
        if (!empty($_REQUEST[$params['name']])) $params['value'] = $_REQUEST[$params['name']];
        return $params;
    }

    public static function text($params)
    {
        return self::field($params);
    }

    public static function password($params)
    {
        return self::field($params);
    }

    public static function checkbox($params)
    {
        return self::field($params);
    }

}