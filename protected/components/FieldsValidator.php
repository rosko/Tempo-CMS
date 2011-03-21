<?php

class FieldsValidator extends CValidator
{
    public $config=array();
    public $language = null;
    
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
        if (!$this->language) $this->language = Yii::app()->language;

        if (!empty($this->config)) {
            $vm = new VirtualModel($this->config, 'FieldSet', $value);
            $vm->validate();
            foreach ($vm->getErrors() as $attr => $v) {
                if (is_array($v)) foreach ($v as $i => $message) {
                    $this->addError($object, $attribute, $message);
                }
            }
        }
    }
}