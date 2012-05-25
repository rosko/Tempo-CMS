<?php

class FileList extends CInputWidget
{
    public $size = 1;
    public $cssClassName = 'cms-filemanager';
    public $options = array();
    public $element = array();

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

/*
        if($this->hasModel())
            echo CHtml::activeHiddenField($this->model,$this->attribute,$this->htmlOptions);
        else
            echo CHtml::hiddenField($name,$this->value,$this->htmlOptions);
*/
        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;
        if (!is_array($value)) $value = @unserialize($value);

        $this->registerClientScript();
        $this->render('FileList', array(
                'id' => $id,
                'name' => $name,
                'value' => $value,
                'size' => $this->size,
                'element' => $this->element,
                'cssClassName' => $this->cssClassName,
        ));
    }

    protected function registerClientScript()
    {
        $cs = Yii::app()->getClientScript();

        $language = Yii::app()->language;
        $csrfTokenName = Yii::app()->getRequest()->csrfTokenName;
        $csrfToken = Yii::app()->getRequest()->getCsrfToken();

        if (!Yii::app()->request->isAjaxRequest) {
            $cs->registerPackage('jquery.uicss');
            $cs->registerPackage('cmsDialogs');
            $cs->registerScript('all', <<<JS

        $.data(document.body, 'language', '{$language}');
        $.data(document.body, 'csrfTokenName', '{$csrfTokenName}');
        $.data(document.body, 'csrfToken', '{$csrfToken}');

JS
);
        }


    }

}