<?php

class UnitText extends Content
{
	const ICON = '/images/icons/fatcow/16x16/text_dropcaps.png';
    const HIDDEN = false;
	
    public function name()
    {
        return Yii::t('UnitText.unit', 'Text');
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'units_text';
	}

	public function rules()
	{
		return $this->localizedRules(array(
			array('unit_id, text', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
			array('author', 'length', 'max'=>64),
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
			'text' => Yii::t('UnitText.unit', 'Text'),
			'author' => Yii::t('UnitText.unit', 'Author'),
		);
	}
	
	public static function form()
	{
		return array(
			'elements'=>array(
				'text'=>array(
					'type'=>'VisualTextAreaFCK',
				),
				'author'=>array(
					'type'=>'ComboBox',
					'showAllValues' => true,
                    'canEdit' => true,
				)
			),
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