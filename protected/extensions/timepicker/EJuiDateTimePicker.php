<?php
/**
 * EJuiDateTimePicker class file.
 *
 * @author Michiel Betel <mbetel@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2010 Michiel Betel
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiInputWidget');

/**
 * EJuiDateTinePicker displays a datetimepicker.
 *
 * CJuiDateTimePicker encapsulates the {@link http://jqueryui.com/demos/datepicker/ JUI
 * datepicker} plugin extended with the timepicker plugin from Trent Richardson
 * {@link http://trentrichardson.com/examples/timepicker/ }
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->widget('zii.widgets.jui.EJuiDateTimePicker', array(
 *     'name'=>'publishDate',
 *     // additional javascript options for the date picker plugin
 *     'options'=>array(
 *         'showAnim'=>'fold',
 *     ),
 *     'htmlOptions'=>array(
 *         'style'=>'height:20px;'
 *     ),
 * ));
 * </pre>
 *
 * By configuring the {@link options} property, you may specify the options
 * that need to be passed to the JUI datepicker plugin. Please refer to
 * the {@link http://jqueryui.com/demos/datepicker/ JUI datepicker} documentation
 * for possible options (name-value pairs).
 *
 * @author Michiel Betel <mbetel@gmail.com>
 */
class EJuiDateTimePicker extends CJuiInputWidget
{
	/**
	 * @var string the locale ID (e.g. 'fr', 'de') for the language to be used by the date picker.
	 * If this property is not set, I18N will not be involved. That is, the date picker will show in English.
	 */
	public $language;

	/**
	 * @var string The i18n Jquery UI script file. It uses scriptUrl property as base url.
	 */
	public $i18nScriptFile = 'jquery-ui-i18n.min.js';
	
	/**
	* Define the needed extra files for the Timepicker
	* 
	*/
	public $extraScriptFile = "jquery-ui-timepicker-addon.min.js";
	public $extraCssFile = "jquery-ui-timepicker-addon.css";

    public $timePickerOnly;

	public function init()
	{
		parent::init();
		// Register the extension script and needed Css - different $baseUrl from the zii stuff
		$basePath=Yii::getPathOfAlias('application.extensions.timepicker.assets');
		$baseUrl=Yii::app()->getAssetManager()->publish($basePath);
		$cs=Yii::app()->getClientScript();
		$cs->registerCssFile($baseUrl.'/'.$this->extraCssFile);
		// NOTE CJuiWidget registers the ui js at POS_END so we should too, otherwise the inclusion order is wrong
		$cs->registerScriptFile($baseUrl.'/'.$this->extraScriptFile, CClientScript::POS_END);    
	}
	
	/**
	 * Run this widget.
	 * This method registers necessary javascript and renders the needed HTML code.
	 */
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
		
		if($this->hasModel())
			echo CHtml::activeTextField($this->model,$this->attribute,$this->htmlOptions);
		else
			echo CHtml::textField($name,$this->value,$this->htmlOptions);
		
		$options=CJavaScript::encode($this->options);
		if(isset($this->timePickerOnly))
			$js = "jQuery('#{$id}').timepicker($options);";
		else
			$js = "jQuery('#{$id}').datetimepicker($options);";
		
		if (isset($this->language)){
			$this->registerScriptFile($this->i18nScriptFile);
			if(isset($this->timePickerOnly))
    			$js = "jQuery('#{$id}').timepicker(jQuery.extend({showMonthAfterYear:false}, jQuery.datepicker.regional['{$this->language}'], {$options}));";
//				$js .= "\njQuery('#{$id}').timepicker('option', jQuery.datepicker.regional['{$this->language}']);";
			else
    			$js = "jQuery('#{$id}').datetimepicker(jQuery.extend({showMonthAfterYear:false}, jQuery.datepicker.regional['{$this->language}'], {$options}));";
//                $js .= "\njQuery('#{$id}').datetimepicker('option', jQuery.datepicker.regional['{$this->language}']);";
				
		}
        $js .= "setTimeout(function() { jQuery('.ui-datepicker').hide();}, 100);";
		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$id, $js);
	}
}