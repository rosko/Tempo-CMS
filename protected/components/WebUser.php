<?php
class WebUser extends CWebUser
{
    public function init()
    {
        $conf = Yii::app()->session->cookieParams;
        $this->identityCookie = array(
            'path' => $conf['path'],
            'domain' => $conf['domain'],
        );
        parent::init();
    }
}