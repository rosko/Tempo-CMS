<?php

class InstallCommandRequired
{
    protected static function version_compare($product, $version, $params)
    {
        $message = '';
        if (empty($params['title'])) $params['title'] = '{product} version';
        $params['status'] = true;
        if (isset($params['min'])) {
            $params['status'] = $params['status'] && version_compare($version,$params['min'],">=");
            $message .= ' {min} or higher';
            $params['t']['{min}'] = $params['min'];
        }

        if (isset($params['max'])) {
            $params['status'] = $params['status'] && version_compare($version,$params['max'],"<=");
            if (!empty($message)) $message .= ' or';
            $message .= ' {max} or lower';
            $params['t']['{max}'] = $params['max'];
        }

        if (isset($params['is'])) {
            $params['status'] = $params['status'] && version_compare($version,$params['is'],"==");
            if (!empty($message)) $message .= ' or';
            $message .= ' {is}';
            $params['t']['{is}'] = $params['is'];
        }
        $message = '{product}' . $message .' is required.';
        $params['t']['{product}'] = $product;
        if (empty($params['message'])) $params['message'] = $message;
        return $params;
    }

    public static function php($params)
    {
        return self::version_compare('PHP', PHP_VERSION, $params);
    }

    public static function mysql($params)
    {
        return self::version_compare('MySQL', Yii::app()->db->getServerVersion(), $params);
    }

    public static function apache($params)
    {
        return self::version_compare('Apache', str_ireplace('Apache/', '', apache_get_version()), $params);
    }

    public static function serverVar($params)
    {
        if (empty($params['title'])) $params['title'] = '$_SERVER variable';
        $params['status'] = true;
        $vars=array('HTTP_HOST','SERVER_NAME','SERVER_PORT','SCRIPT_NAME','SCRIPT_FILENAME','PHP_SELF','HTTP_ACCEPT','HTTP_USER_AGENT');
        $missing=array();
        foreach($vars as $var)
        {
            if(!isset($_SERVER[$var]))
                $missing[]=$var;
        }
        if(!empty($missing)) {
            $params['message'] = '$_SERVER does not have {vars}.';
            $params['t']['{vars}'] = implode(', ',$missing);
        }
        if(realpath($_SERVER["SCRIPT_FILENAME"]) !== Yii::getPathOfAlias('webroot.index').'.php')
            $params['message'] = '$_SERVER["SCRIPT_FILENAME"] must be the same as the entry script file path.';

        if(!isset($_SERVER["REQUEST_URI"]) && isset($_SERVER["QUERY_STRING"]))
            $params['message'] = 'Either $_SERVER["REQUEST_URI"] or $_SERVER["QUERY_STRING"] must exist.';

        if(!isset($_SERVER["PATH_INFO"]) && strpos($_SERVER["PHP_SELF"],$_SERVER["SCRIPT_NAME"]) !== 0)
            $params['message'] = 'Unable to determine URL path info. Please make sure $_SERVER["PATH_INFO"] (or $_SERVER["PHP_SELF"] and $_SERVER["SCRIPT_NAME"]) contains proper value.';

        return $params;
    }

    public static function gd($params)
    {
        if (empty($params['title'])) {
            $params['title'] = '{name} extension';
            $params['t']['{name}'] = 'GD';
        }
        $params['status'] = false;
        if(extension_loaded('gd'))
        {
            $params['status'] = true;
            $gdinfo=gd_info();
            if (!empty($params['needFreeType']) && !$gdinfo['FreeType Support']) {
                $params['status'] = false;
                $params['message'] = 'GD installed<br />FreeType support not installed';
            }
        } else $params['message'] = 'GD not installed';

        return $params;
    }

    public static function extension($params)
    {
        if (empty($params['title'])) {
            $params['title'] = '{name} extension';
            $params['t']['{name}'] = strtoupper($params['name']);
        }
        $params['status'] = extension_loaded($params['name']);
        return $params;
    }

    public static function phpClass($params)
    {
        if (empty($params['title'])) {
            $params['title'] = '{name} class';
            $params['t']['{name}'] = $params['name'];
        }
        $params['status'] = class_exists($params['name'], false);
        return $params;
    }

    public static function phpFunction($params)
    {
        if (empty($params['title'])) {
            $params['title'] = '{name} function';
            $params['t']['{name}'] = $params['name'];
        }
        $params['status'] = function_exists($params['name']);
        return $params;
    }

    protected function filesystemItem($type, $params)
    {
        // $params['force'] - создает файл/папки или дает им нужные права
        $type_funcs = array(
            'folder'=>array(
                'readable'=>'is_dir',
                'writable'=>'is_writable',
                'force'=>array(
                    'readable'=>array('mkdir', '0755', true),
                    'writable'=>array('chmod', '0777'),
                ),
            ),
            'file'=>array(
                'readable'=>'is_file',
                'writable'=>'is_writable',
                'force'=>array(
                    'readable'=>array('file_put_contents', ''),
                    'writable'=>array('chmod', '0666'),
                ),
            ),
        );
        if (!empty($params['alias'])) {
            $params['name'] = Yii::getPathOfAlias($params['alias']).$params['name'];
        }
        
        if (empty($params['title'])) {
            $params['title'] = ucfirst($type) .' {name} ' . $params['is'];
            $params['t']['{name}'] = $params['name'];
        }
        if (!isset($params['force'])) $params['force'] = true;
        $params['status'] = true;
        if (!empty($params['is']) && !empty($params['name'])) {
            if ($params['is']=='writeable') $params['is'] = 'writable';
            $params['status'] = call_user_func($type_funcs[$type][$params['is']], $params['name']);
            if (!$params['status'] && !empty($params['foce']))
            {
                $data = array_slice($type_funcs[$type]['force'][$params['is']], 1);
                array_unshift($data, $params['name']);
                @call_user_func_array($type_funcs[$type]['force'][$params['is']][0], $data);
                $params['status'] = call_user_func($type_funcs[$type][$params['is']], $params['name']);
            }
        }
        return $params;
    }

    public static function folder($params)
    {
        return self::filesystemItem('folder', $params);
    }

    public static function file($params)
    {
        return self::filesystemItem('file', $params);
    }

}