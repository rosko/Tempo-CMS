<?php
class InstallModelRequirements extends CFormModel
{
    public $requirements;

    public function rules()
    {
        return array(
            array('requirements', 'safe')
        );
    }

    public function form()
    {
        return array(
            'elements'=>array(
                'requirements'=>array(
                    'type'=>'hidden',
                ),
            ),
        );
    }

}