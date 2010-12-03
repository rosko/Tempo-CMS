<?php

class ThemeSelect extends CInputWidget
{
    public $empty = '«as in general settings»';

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

        $data = array_combine(Yii::app()->themeManager->themeNames, Yii::app()->themeManager->themeNames);
        if ($this->empty)
            $data = array_merge(array(''=>Yii::t('cms', $this->empty)), $data);

        if($this->hasModel())
            echo CHtml::activeDropDownList($this->model,$this->attribute,$data, $this->htmlOptions);
        else
            echo CHtml::dropDownList($name,$this->value,$data,$this->htmlOptions);
    }
}
