<?php

class Area extends CWidget
{
    public $name;
    
    public function run()
    {
        $this->render('area', array('name'=>$this->name));
    }
}
