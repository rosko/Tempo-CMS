<?php

class AccessModel extends CFormModel
{
    var $params;

    public function rules()
    {
        return array(
            array('params', 'safe'),
        );
    }

    public function form()
    {
        return array(
            'type' => 'form',
            'id' => 'AccessModel',
            'elements' => array(
                'params' => array(
                    'type' => 'AccessRights',
                ),
            ),
        );
    }


}