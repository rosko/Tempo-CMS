<?php

class Captcha extends CInputWidget
{
    public $captchaAction = 'site/captcha';
    public $showRefreshButton=true;
    public $clickableImage=true;
    public $buttonLabel;
    public $buttonType='link';
    public $imageOptions=array();
    public $buttonOptions=array(); 
    
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
        
        $this->widget('CCaptcha', array(
            'id'=>$id,
            'captchaAction'=>$this->captchaAction,
            'showRefreshButton'=>$this->showRefreshButton,
            'clickableImage'=>$this->clickableImage,
            'buttonLabel'=>$this->buttonLabel,
            'buttonType'=>$this->buttonType,
            'imageOptions'=>$this->imageOptions,
            'buttonOptions'=>$this->buttonOptions,            
        ));
        echo '<br />';
        if ($this->hasModel()) {
            echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
        } else {
            echo CHtml::textField($name, $this->value, $this->htmlOptions);
        }
    }
}