<?php

Yii::import('zii.widgets.jui.CJuiSliderInput');

class Slider extends CJuiSliderInput
{
    public function init()
    {
        $this->themeUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.css.jui'));
        $this->theme = Yii::app()->params['juiTheme'];
    }

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

        if($this->hasModel()===false && $this->value!==null)
                $this->options['value']=$this->value;

        if($this->hasModel()) {
                echo CHtml::activeTextField($this->model,$this->attribute,$this->htmlOptions);
                $this->options['value'] = $this->model->{$this->attribute};
        }
        else
                echo CHtml::textField($name,$this->value,$this->htmlOptions);

        $idHidden = $this->htmlOptions['id'];
        $nameHidden = $this->htmlOptions['name'];

        $this->htmlOptions['id']=$idHidden.'_slider';
        $this->htmlOptions['name']=$nameHidden.'_slider';

        echo CHtml::openTag($this->tagName,$this->htmlOptions);
        echo CHtml::closeTag($this->tagName);


        $this->options[$this->event]= 'js:function(event, ui) { jQuery(\'#'. $idHidden .'\').val(ui.value); }';


        $options=empty($this->options) ? '' : CJavaScript::encode($this->options);

        $js = "jQuery('#{$id}_slider').slider($options);\n";
        
        $js .= <<<JS
        
jQuery('#{$idHidden}').keyup(function () {
    $('#{$id}_slider').slider('option', 'value', $(this).val());
    $('#{$id}_slider').trigger('change');
});
        
JS;
        $cs = Yii::app()->getClientScript();
        $cs->registerScript(__CLASS__.'#'.$id, $js);        
    }

}
