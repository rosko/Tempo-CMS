<?php

class ImageHelper
{
    public static function resizeDown($image, $width, $height)
    {
        if (substr($image, 0, 1)=='/' || ini_get('allow_url_fopen')) {
            if (substr($image, 0, 1)=='/') {
                $sourcePath = Yii::getPathOfAlias('webroot').$image;
            } else{
                $sourcePath = $image;
            }
            $extension = FileHelper::getExtension($sourcePath);
            $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
            $resizeUrl = '/files/.resized/'.$filename.'_'.substr(md5($sourcePath), 0, 8).'_'.$width.'x'.$height.'.'.$extension;
            $resizePath = Yii::getPathOfAlias('webroot').$resizeUrl;
            if (!is_dir(dirname($resizePath))) {
                mkdir(dirname($resizePath));
                @chmod(dirname($resizePath), 0777);
            }
            Yii::import('application.vendors.wideimage.WideImage', true);
            WideImage::load($sourcePath)
                ->resizeDown($width, $height)
                ->saveToFile($resizePath);
            $image = $resizeUrl;
        }
        return $image;
    }
}