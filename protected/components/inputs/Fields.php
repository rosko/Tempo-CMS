<?php

class Fields extends CInputWidget
{
    public $config;
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

        
        if (!$this->language) $this->language = Yii::app()->language;
        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;

        if (!empty($this->config)) {
            $vm = new VirtualModel($this->config, 'FieldSet', $value);
            $form = new CForm($vm->formMap, $vm);
            echo str_replace('VirtualModel_', str_replace(array('[',']','-'),'_',$name).'_', str_replace('VirtualModel[', $name.'[', str_ireplace('</form>', '', preg_replace('/<form([^>]*)>/msi', '', $form->render()))));
        }
        
    }

}