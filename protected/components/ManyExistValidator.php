<?php

class ManyExistValidator extends CExistValidator
{

	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;

		$className=$this->className===null?get_class($object):Yii::import($this->className);
		$attributeName=$this->attributeName===null?$attribute:$this->attributeName;
		$finder=CActiveRecord::model($className);
		$table=$finder->getTableSchema();
        if (!is_array($attributeName)) $attributeName = array($attributeName);
        $condition=array();
        foreach ($attributeName as $aN) {
            if(($column=$table->getColumn($aN))===null)
                throw new CException(Yii::t('yii','Table "{table}" does not have a column named "{column}".',
                    array('{column}'=>$aN,'{table}'=>$table->name)));
            $condition[] = $column->rawName.'=:vp';
        }

		$criteria=array('condition'=>implode(' OR ', $condition),'params'=>array(':vp'=>$value));
		if($this->criteria!==array())
		{
			$criteria=new CDbCriteria($criteria);
			$criteria->mergeWith($this->criteria);
		}

		if(!$finder->exists($criteria))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} "{value}" is invalid.');
			$this->addError($object,$attribute,$message,array('{value}'=>$value));
		}
	}


}