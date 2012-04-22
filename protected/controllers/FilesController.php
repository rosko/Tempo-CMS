<?php

 // Сделать действие для elFinder, чтобы можно было создавать уменьшенные картинки заранее определенного размера

class FilesController extends Controller
{
    public $layout = 'empty';

    public function connectorOptions()
    {
        return array(
            'roots' => array(
                array(
                    'driver'  => "LocalFileSystem",
                    'path' => Yii::getPathOfAlias('webroot.files'),
                    'URL' => "/files",
                    'tmbPath' => '.thumb',
                    'accessControl' => array(new FileHelper, 'elfAccess'),
                )
            ),
//            'bind' => array(
//                'upload' => array(new FileHelper, 'elfUpload'),
//            ),
        );

    }

    public function filters()
    {
        return array('accessControl');
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('managerConnector', 'manager'),
                'users'=>array('@'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function actionManagerConnector($mode='')
    {
        FileHelper::elfRunConnector($this->connectorOptions());
    }

    public function actionManager()
    {
        $this->render('manager');
    }

}