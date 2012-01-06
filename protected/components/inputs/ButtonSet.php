<?php

Yii::import('zii.widgets.jui.CJuiInputWidget');

class ButtonSet extends CJuiInputWidget
{
    public $buttons = array();
    public $kind = 'radiolist';

    public function init()
    {
        $this->themeUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.css.jui'));
        $this->theme = Yii::app()->params['juiTheme'];
        parent::init();
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

        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;

        $this->beginWidget('zii.widgets.jui.CJuiButton', array(
            'buttonType'=>'buttonset',
            'name'=>$name,
        ));
        echo self::activeRadioButtonList($this->model, $this->attribute, $this->buttons, array(
            'separator'=>'',
        ));
        $this->endWidget();
        
?><style type="text/css">
    #<?=$id?> {
        clear:both;
    }
    #<?=$id?> label {
        float:left;
        display:inline;
    }
</style><?php
    }
    
	public static function activeRadioButtonList($model,$attribute,$data,$htmlOptions=array())
	{
		CHtml::resolveNameID($model,$attribute,$htmlOptions);
		$selection=CHtml::resolveValue($model,$attribute);
		if($model->hasErrors($attribute))
			CHtml::addErrorCss($htmlOptions);
		$name=$htmlOptions['name'];
		unset($htmlOptions['name']);

		if(array_key_exists('uncheckValue',$htmlOptions))
		{
			$uncheck=$htmlOptions['uncheckValue'];
			unset($htmlOptions['uncheckValue']);
		}
		else
			$uncheck='';

		$hiddenOptions=isset($htmlOptions['id']) ? array('id'=>CHtml::ID_PREFIX.$htmlOptions['id']) : array('id'=>false);
		$hidden=$uncheck!==null ? CHtml::hiddenField($name,$uncheck,$hiddenOptions) : '';

		return $hidden . self::radioButtonList($name,$selection,$data,$htmlOptions);
	}

	public static function radioButtonList($name,$select,$data,$htmlOptions=array())
	{
		$template=isset($htmlOptions['template'])?$htmlOptions['template']:'{input} {label}';
		$separator=isset($htmlOptions['separator'])?$htmlOptions['separator']:"<br/>\n";
		unset($htmlOptions['template'],$htmlOptions['separator']);

		$labelOptions=isset($htmlOptions['labelOptions'])?$htmlOptions['labelOptions']:array();
		unset($htmlOptions['labelOptions']);

		$items=array();
		$baseID=CHtml::getIdByName($name);
		$id=0;
		foreach($data as $value=>$item)
		{
			$checked=!strcmp($value,$select);
			$htmlOptions['value']=$value;
			$labelOptions['id']=$baseID.'_label_'.$id;
			$htmlOptions['id']=$baseID.'_'.$id++;
            $labelOptions['title']=$item['title'];
            $htmlOptions['class']='ui-icon ui-state-default '.$item['class'];
			$option=CHtml::radioButton($name,$checked,$htmlOptions);
            if (!empty($item['icon'])) {
                $item['caption'] = '<span style="float:left;" class="ui-icon '.$item['icon'].'"> '.$item['caption'].'</span>' . $item['caption'];
            }
            
			$label=CHtml::label($item['caption'],$htmlOptions['id'],$labelOptions);            
			$items[]=strtr($template,array('{input}'=>$option,'{label}'=>$label));
		}
		return CHtml::tag('span',array('id'=>$baseID),implode($separator,$items));
	}
    
}
