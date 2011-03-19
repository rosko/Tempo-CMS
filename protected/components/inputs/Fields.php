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

        $config = array();
        foreach ($this->config as $k => $v) {

            $_n = $v['name'];
            unset($v['name']);

            if (isset($value[$_n]))
                $v['default'] = $value[$_n];

            $v['label'] = $v['label'][$this->language];
            $v['hint'] = $v['hint'][$this->language];

            if (isset($v['rules'])) foreach ($v['rules'] as $k2 => $v2) {
                array_unshift($v2, $_n);
                $v['rules'][$k2] = $v2;
            }

            $config[$_n] = $v;
        }
        $vm = new VirtualModel($config);
        $form = new CForm($vm->formMap, $vm);
        echo str_replace('VirtualModel_', str_replace(array('[',']','-'),'_',$name).'_', str_replace('VirtualModel[', $name.'[', str_ireplace('</form>', '', preg_replace('/<form([^>]*)>/msi', '', $form->render()))));

    }

}