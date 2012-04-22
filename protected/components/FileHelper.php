<?php

class FileHelper extends CFileHelper
{
    public function elfAccess($attr, $path, $data, $volume)
    {
        return strpos(basename($path), '.') === 0
            ? !($attr == 'read' || $attr == 'write')
            :  null;
    }

    public function elfUpload($cmd, $result, $args, $elfinder)
    {
        if ($cmd == 'upload') {
            if (isset($args['datedir'])) {
                //print_R ($result);
            }
        }
        return true;
    }

    public function elfRunConnector($options)
    {
        $elFinderPath = Yii::getPathOfAlias('application.vendors.elfinder2.php');
        require_once $elFinderPath . DIRECTORY_SEPARATOR . 'elFinderConnector.class.php';
        require_once $elFinderPath . DIRECTORY_SEPARATOR . 'elFinder.class.php';
        require_once $elFinderPath . DIRECTORY_SEPARATOR . 'elFinderVolumeDriver.class.php';
        require_once $elFinderPath . DIRECTORY_SEPARATOR . 'elFinderVolumeLocalFileSystem.class.php';

        // Defining default connector options
        $connector = new elFinderConnector(new elFinder($options));
        $connector->run();
    }

}