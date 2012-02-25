<?php

class ModelText extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/text_dropcaps.png';
    }
    
    public function modelName($language=null)
    {
        return Yii::t('UnitText.main', 'Text', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_text';
	}

	public function rules()
	{
		return $this->localizedRules(array(
			array('text', 'required'),
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
//			array('author', 'length', 'max'=>64, 'encoding'=>'UTF-8'),
		));
	}

    public function i18n()
    {
        return array('text', 'author');
    }

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
			'text' => Yii::t('UnitText.main', 'Text'),
//			'author' => Yii::t('UnitText.main', 'Author'),
		);
	}
	
	public static function form()
	{
		return array(
			'elements'=>array(
				'text'=>array(
					'type'=>'TextEditor',
                    'kind'=>'ck',
                    'config'=>array(
                        'toolbar'=>'Basic',
                    )
				),
/*				'author'=>array(
					'type'=>'ComboBox',
					'showAllValues' => true,
                    'canEdit' => true,
				)*/
			),
		);
	}

    public function scheme()
    {
        return array(
            'unit_id' => 'integer unsigned',
            'text' => 'text',
            'author' => 'string',
        );
    }

    public function resizableObjects()
    {
        return array(
            'img' => array(
                'attributes' => 'text',
                'aspectRatio' => false,
            ),
        );
    }

}

