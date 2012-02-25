<?php

class TestController extends CController
{
    public function actionIndex($path='/')
    {
        $fsw = new FilesystemWrapper('/home/rosko/WWW/hosts/test/public_html/test/', array(
            'baseUrl' => 'http://test/test/',
        ));
        $fsw->filter = array('excludeHidden' => true);
        $fsw->sort = array('directoriesFirst' => true);

        if ($fsw) {
            echo CHtml::beginForm('', 'post', array('enctype'=>'multipart/form-data'));
            echo CHtml::fileField('file');
            echo CHtml::submitButton();
            echo CHtml::endForm();
            if (CUploadedFile::getInstanceByName('file')) {
                echo $fsw->uploadFile('/', 'file', true, 'upload.jpg');
            }
            //echo $fsw->delete('/mydir.txt');
            //print_r ($fsw->createFile('/', 'mydir.txt', 'sdfsdfsвавів'));
            print_r ($fsw->getDirectory($path));
        }
    }
}