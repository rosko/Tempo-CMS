<?php

class Select extends CInputWidget
{
    public $options = array();
    public $input = 'dropdownlist';
    public $attributes = array();
    public $language = null;

    public function run()
	{
        list($name,$id)=$this->resolveNameID();

		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;

		if(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];
        else
            $this->htmlOptions['name']=$name;

        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;
        $items = array();
        if (!$this->language) $this->language = Yii::app()->language;
        foreach ($this->options as $k => $v) {
            if (is_array($v)) {
                $items[$v[$this->language]] = $v[$this->language];
            } else {
                $items[$v] = $v;
            }
        }

        $allowedInputs = array(
            'listbox'=>'activeListBox',
            'dropdownlist'=>'activeDropDownList',
            'checkboxlist'=>'activeCheckBoxList',
            'radiolist'=>'activeRadioButtonList',
        );
		if(isset($allowedInputs[$this->input]))
		{
            if ($this->hasModel()) {
    			$method=$allowedInputs[$this->input];
                echo CHtml::$method($this->model, $this->attribute, $items, $this->attributes);
            } else {
    			$method=substr($allowedInputs[$this->input],6);
                echo CHtml::$method($this->attribute, $this->value, $items, $this->attributes);
            }
		}


    }

}