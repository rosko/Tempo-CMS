<?php

class UnitImage extends Content
{
	const ICON = '/images/icons/fatcow/16x16/image.png';
    const HIDDEN = false;

    public function name()
    {
        return Yii::t('UnitImage.unit', 'Image');
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_image';
	}

	public function rules()
	{
		return array(
			array('unit_id, image, width, height', 'required'),
			array('unit_id, width, height', 'numerical', 'integerOnly'=>true),
			array('image, url', 'length', 'max'=>255),
            array('target', 'length', 'max'=>50)
		);
	}

    public function settings()
    {
        return array_merge(parent::settings(__CLASS__), array(
            'show_border' => array(
                'type'=>'checkbox',
                'label'=>Yii::t('UnitImage.unit', 'Show border'),
            )
        ));
    }
    public function settingsRules()
    {
        return array_merge(parent::settingsRules(), array(
            array('show_border', 'boolean')
        ));
    }

	public function attributeLabels()
	{
		return array(
			'image' => Yii::t('UnitImage.unit', 'Image'),
			'width' => Yii::t('UnitImage.unit', 'Width'),
			'height' => Yii::t('UnitImage.unit', 'Height'),
			'url' => Yii::t('UnitImage.unit', 'Link'),
            'target' => Yii::t('UnitImage.unit', 'Link target'),
		);
	}

	public static function form()
	{
		$className = __CLASS__;
		$slideWidth = <<<EOD
js:function(event,ui) {
	$('#{$className}_width').val(ui.value);
	{$className}_makesize(ui.value, false, ui.handle);
}
EOD;
		$changeWidth = <<<EOD
js:function(event,ui) {
	{$className}_makesize(ui.value, false, ui.handle);
}
EOD;
		$slideHeight = <<<EOD
js:function(event,ui) {
	$('#{$className}_height').val(ui.value);
	{$className}_makesize(ui.value, true, ui.handle);
}
EOD;
		$changeHeight = <<<EOD
js:function(event,ui) {
	{$className}_makesize(ui.value, true, ui.handle);
}
EOD;
		
		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitImage.unit', 'Image')),
				'image'=>array(
					'type'=>'Link',
					'size'=>40,
					'showPageSelectButton'=>false,
					'extensions'=>array('jpg', 'jpeg', 'gif', 'png'),
					'onChange'=> "js:$('#cms-pageunit-'+pageunit_id).find('img').attr('src', $(this).val());"
				),
                self::renderFile($className, 'files.imagesize'),
				'width'=>array(
					'type'=>'Slider',
					'event'=>'none',
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
					'options'=>array(
						'min' => 1,
						'max' => 2000,
						'step' => 1,
						'slide' => $slideHeight,
						'change' => $changeHeight
					)
				),
                Form::tab(Yii::t('UnitImage.unit', 'Link')),
				'url'=>array(
					'type'=>'Link',
					'size'=>40,
					'showUploadButton'=>false
				),
                'target'=>array(
                    'type'=>'dropdownlist',
                    'items'=> array(
                        '' => Yii::t('UnitImage.unit', 'Current window'),
                        '_blank' => Yii::t('UnitImage.unit', 'New window'),
                    ),
                ),
			),
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