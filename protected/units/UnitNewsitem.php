<?php

class UnitNewsitem extends Content
{
	const NAME = "Новость";
	const ICON = '/images/icons/iconic/green/comment_alt1_fill_16x16.png';
    const HIDDEN = false;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_newsitem';
	}

	public function rules()
	{
		return array(
			array('unit_id, text, date', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
			array('source', 'length', 'max'=>64),
			array('url', 'length', 'max'=>255),
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'unit_id' => 'Unit',
			'text' => 'Текст',
			'date' => 'Дата',
			'source' => 'Источник',
			'url' => 'Ссылка на источник',
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
				'text'=>array(
					'type'=>'VisualTextAreaFCK',
				),
				'date'=>array(
					'type'=>'DatePicker',
					'language'=>'ru',
					'options'=> array(
						'dateFormat'=>'yy-mm-dd',
//						'showOtherMonths'=>'true',
//						'selectOtherMonths'=>'true'
					)
				),
				'source'=>array(
					'type'=>'text',
					'maxlength'=>64
				),
				'url'=>array(
					'type'=>'Link',
					//'maxlength'=>255
				)
			),
		);
	}
	
	public function scopes()
	{
		return array(
			'default' => array(
				'order'=>'date DESC',
				'condition'=>'date <= NOW()'
			),
			'imported'=>array(
				'condition'=>'source <> "" OR url <> ""'
			),
		);
	}
	
	public function namedScopes()
	{
		return array(
			'recently'=>array(
				'limit'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 1,
						'max' => 20,
					)
				)
			)
		);
	}
	
	public function recently($limit=5)
	{
		$this->getDbCriteria()->mergeWith(array(
			'limit'=>$limit
		));
		return $this;
	}
	
}