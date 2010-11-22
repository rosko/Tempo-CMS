<?php

class TemplateSelect extends CInputWidget
{
    public $className;
    public $empty = '«обычный»';

    public function run()
	{
        list($name,$id)=$this->resolveNameID();

		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;

		if(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];

        $className = $this->className;

        $data = $className::getTemplates($className);
        if ($data != array()) {
            $data = array_merge(array(''=>$this->empty), $data);

            if($this->hasModel())
                echo CHtml::activeDropDownList($this->model,$this->attribute,$data, $this->htmlOptions);
            else
                echo CHtml::dropDownList($name,$this->value,$data,$this->htmlOptions);
        }


    }


}
