<?php

class VisualTextAreaRedactor extends CInputWidget
{
    public $textarea = true;
    public $path = '/';
    public $fullscreen = false;
    public $autosave = false;
    public $saveInterval = 60;
    public $resize = true;
    public $visual = true;
    public $focus = false;
    public $toolbar = 'original';
    public $upload = 'upload.php';
    public $uploadParams = '';
    public $uploadFunction = false;
    public $width = false;
    public $height = false;
    public $autoformat = true;
    public $colors = array(
        '#ffffff', '#000000', '#eeece1', '#1f497d', '#4f81bd', '#c0504d', '#9bbb59', '#8064a2', '#4bacc6', '#f79646',
        '#f2f2f2', '#7f7f7f', '#ddd9c3', '#c6d9f0', '#dbe5f1', '#f2dcdb', '#ebf1dd', '#e5e0ec', '#dbeef3', '#fdeada',
        '#d8d8d8', '#595959', '#c4bd97', '#8db3e2', '#b8cce4', '#e5b9b7', '#d7e3bc', '#ccc1d9', '#b7dde8', '#fbd5b5',
        '#bfbfbf', '#3f3f3f', '#938953', '#548dd4', '#95b3d7', '#d99694', '#c3d69b', '#b2a2c7', '#b7dde8', '#fac08f',
        '#a5a5a5', '#262626', '#494429', '#17365d', '#366092', '#953734', '#76923c', '#5f497a', '#92cddc', '#e36c09',
        '#7f7f7f', '#0c0c0c', '#1d1b10', '#0f243e', '#244061', '#632423', '#4f6128', '#3f3151', '#31859b', '#974806');
    public $options=array();
    
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

        $this->htmlOptions['style'] = 'width:800px;height:400px;';
            
        $this->registerClientScript();

        if($this->hasModel())
            echo CHtml::activeTextArea($this->model,$this->attribute,$this->htmlOptions);
        else
            echo CHtml::textArea($name,$this->value,$this->htmlOptions);

    }

    public function registerClientScript()
    {
        $id=$this->htmlOptions['id'];

        $rOptions=$this->getClientOptions();
        $options=$rOptions===array()?'{}' : CJavaScript::encode($rOptions);

        $cs=Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');
        $cs->registerScriptFile('/3rdparty/redactor/redactor/redactor.js',CClientScript::POS_END);
        $cs->registerCssFile('/3rdparty/redactor/redactor/css/redactor.css');
        
        $cs->registerScript('Yii.VisualTextAreaRedactor#'.$id,"jQuery(\"#{$id}\").redactor({$options});");
        
    }
    
    protected function getClientOptions()
    {
        static $properties=array(
            'textarea', 'path', 'fullscreen', 'autosave', 'saveInterval', 'resize', 'visual',
            'focus', 'toolbar', 'upload', 'uploadParams', 'width', 'height', 'autoformat',
            'colors');

        static $functions=array('uploadFunction');

        $options=$this->options;
        foreach($properties as $property)
        {
            if($this->$property!==null)
                $options[$property]=$this->$property;
        }
        foreach($functions as $func)
        {
            if(is_string($this->$func) && strncmp($this->$func,'js:',3))
                $options[$func]='js:'.$this->$func;
        }
        return $options;
    }
}
?>