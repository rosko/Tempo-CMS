<?php

class InstallCommandText
{
    public function run($params)
    {
        $params['status'] = true;
        return $params;
    }

}