<?php

class FieldsValidator extends CExistValidator
{
/*
 * Остановился на том, что нужно сделать валидатор для поля Fields
 * а также валидатор для FieldSet
 */
    
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
        //$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} "{value}" is invalid.');
        //$this->addError($object,$attribute,$message,array('{value}'=>$value));
    }
}