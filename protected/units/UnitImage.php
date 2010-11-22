<?php

class UnitImage extends Content
{
	const NAME = "Изображение";
	const ICON = '/images/icons/iconic/green/image_16x16.png';
    const HIDDEN = false;

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
                'label'=>'Показывать рамку'
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
			'id' => 'ID',
			'unit_id' => 'Unit',
			'image' => 'Изображение',
			'width' => 'Ширина',
			'height' => 'Высота',
			'url' => 'Ссылка',
            'target' => 'Открывать ссылку',
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
                Form::tab('Изображение'),
				'image'=>array(
					'type'=>'Link',
					'size'=>40,
					'showPageSelectButton'=>false,
					'extensions'=>array('jpg', 'jpeg', 'gif', 'png'),
					'onChange'=> "js:$('#cms-pageunit-'+pageunit_id).find('img').attr('src', $(this).val());"
				),
                self::renderFile($className, 'imagesize'),
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
                Form::tab('Ссылка'),
				'url'=>array(
					'type'=>'Link',
					'size'=>40,
					'showUploadButton'=>false
				),
                'target'=>array(
                    'type'=>'dropdownlist',
                    'items'=> array(
                        '' => 'В этом же окне',
                        '_blank' => 'В новом окне'
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
}