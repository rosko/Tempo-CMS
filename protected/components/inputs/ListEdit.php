<?php

class ListEdit extends CInputWidget
{
    public $i18n = false;

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
        if (!is_array($value)) {
            $unser = @unserialize($value);
            $value =  $unser===FALSE ? $value : $unser;
        }

        $langs = I18nActiveRecord::getLangs(Yii::app()->language);

        $this->render('ListEdit', array(
            'id' => $id,
            'name' => $name,
            'items' => $value,
            'langs' => $langs,
        ));
    }

}