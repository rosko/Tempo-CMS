<?php

class ModelImage extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/image.png';
    }
    
    public function modelName($language=null)
    {
        return Yii::t('UnitImage.main', 'Image', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_image';
	}

	public function rules()
	{
		return array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('image, width, height', 'required'),
			array('unit_id, width, height', 'numerical', 'integerOnly'=>true),
			array('image, url', 'length', 'max'=>255, 'encoding'=>'UTF-8'),
            array('target', 'length', 'max'=>50, 'encoding'=>'UTF-8')
		);
	}

	public function attributeLabels()
	{
		return array(
			'image' => Yii::t('UnitImage.main', 'Image'),
			'width' => Yii::t('UnitImage.main', 'Width'),
			'height' => Yii::t('UnitImage.main', 'Height'),
			'url' => Yii::t('UnitImage.main', 'Link'),
            'target' => Yii::t('UnitImage.main', 'Link target'),
		);
	}

	public static function form()
	{
		$className = 'image';
		$slideWidth = 'js:function(event,ui)'.<<<JS
 {
	$('#{$className}_width').val(ui.value);
	{$className}_makesize(ui.value, false, ui.handle);
}
JS;
		$changeWidth = 'js:function(event,ui)'.<<<JS
 {
	{$className}_makesize(ui.value, false, ui.handle);
}
JS;
		$slideHeight = 'js:function(event,ui)'.<<<JS
 {
	$('#{$className}_height').val(ui.value);
	{$className}_makesize(ui.value, true, ui.handle);
}
JS;
		$changeHeight = 'js:function(event,ui)'.<<<JS
 {
	{$className}_makesize(ui.value, true, ui.handle);
}
JS;
        $cfg = ContentUnit::loadConfig();
		
		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitImage.main', 'Image')),
				'image'=>array(
					'type'=>'Link',
					'size'=>40,
					'showPageSelectButton'=>false,
					'extensions'=>array('jpg', 'jpeg', 'gif', 'png'),
					'onChange'=> "js:$('#cms-pageunit-'+pageUnitId).find('img').attr('src', $(this).val());"
				),
                Yii::app()->controller->renderPartial($cfg['UnitImage'].'.image.assets.imagesize'),
				'width'=>array(
					'type'=>'Slider',
					'event'=>'none',
                    'htmlOptions'=>array(
                        'size'=>5,
                    ),
					'options'=>array(
						'min' => 1,
						'max' => 2000,
						'step' => 1,
						'slide' => $slideWidth,
						'change' => $changeWidth
					)
				),
				'height'=>array(
					'type'=>'Slider',
					'event'=>'none',
                    'htmlOptions'=>array(
                        'size'=>5,
                    ),
					'options'=>array(
						'min' => 1,
						'max' => 2000,
						'step' => 1,
						'slide' => $slideHeight,
						'change' => $changeHeight
					)
				),
                Form::tab(Yii::t('UnitImage.main', 'Link')),
				'url'=>array(
					'type'=>'Link',
					'size'=>40,
					'showUploadButton'=>false,
                    'showFileManagerButton'=>false,
				),
                'target'=>array(
                    'type'=>'dropdownlist',
                    'items'=> array(
                        '' => Yii::t('UnitImage.main', 'Current window'),
                        '_blank' => Yii::t('UnitImage.main', 'New window'),
                    ),
                ),
			),
		);
	}
	
    public function scheme()
    {
        return array(
            'unit_id' => 'integer unsigned',
            'image' => 'string',
            'width' => 'integer unsigned',
            'height' => 'integer unsigned',
            'url' => 'string',
            'target' => 'char(32)',
        );
    }

    public static function defaultObject()
	{
		$obj = new self;
		$obj->image = 'image';
		$obj->width = 100;
		$obj->height = 100;
		return $obj;
	}

    public function resizableObjects()
    {
        return array(
            'img' => array(
                'attributes' => array('width', 'height'),
                'aspectRatio' => false,
            ),
        );
    }

}
