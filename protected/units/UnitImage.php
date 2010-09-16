<?php

class UnitImage extends CActiveRecord
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
		);
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
		$className = __CLASS__;
		
		return array(
			'elements'=>array(
				'image'=>array(
					'type'=>'Link',
					'size'=>40,
					'showPageSelectButton'=>false,
					'extensions'=>array('jpg', 'jpeg', 'gif', 'png'),
					'onChange'=> "js:$('#cms-pageunit-'+pageunit_id).find('img').attr('src', $(this).val());"
				),
				<<<EOD
<hr />
<select id="{$className}_setsize">
	<option value="">Выберите размер изображения</option>
	<option value="0x0">Действительный</option>
	<option value="88x31">88 x 31</option>
	<option value="100x100">100 x 100</option>
	<option value="150x400">150 x 400</option>
	<option value="200x200">200 x 200</option>
</select><br />
<input type="checkbox" id="{$className}_ratio" checked="checked" /><label style="display:inline;clear:none;" for="{$className}_ratio"> Сохранять пропорции</label>
<script type="text/javascript">
<!--
$('.field_title').hide();

$('#{$className}_setsize').change(function() {

	$('#{$className}_image').change();
	var im = new Image();
	im.src = $('#{$className}_image').val();
	var t = this;
	im.onload = function() {

		var w = $(t).val().substring(0, $(t).val().indexOf('x'));
		var h = $(t).val().substring($(t).val().indexOf('x')+1);
		if ($(t).val()) {
			if ((w > 0) && (h > 0) && (this.height > 0)) {
				if (w > h) {
					var ch = true;
				} else if (w < h) {
					var ch = false;
				} else {
					var ch = this.width < this.height;
				}
				var s = {$className}_fixratio(w, h, this.width/this.height, ch);
				w = s.width;
				h = s.height;
			} else {
				w = this.width;
				h = this.height;
			}
			$('#{$className}_width_slider').slider( "option", "value", w);
			$('#{$className}_width').val(w);
			$('#{$className}_height_slider').slider( "option", "value", h);
			$('#{$className}_height').val(h);
		}

	}
	
});
$('#{$className}_setsize').keydown(function () {
	$('#{$className}_setsize').change();	
});
$('#{$className}_ratio').click(function() {
	$('#{$className}_setsize').change();
});

function {$className}_fixratio(w, h, ratio, height_const)
{
	var r = {};
	if (height_const == null) {
		var height_const = ratio < 1;
	}
	if ($('#{$className}_ratio').attr('checked')) {
		if (height_const) {
			r.width = Math.round(h*ratio);
			r.height = h;
		} else {
			r.width = w;
			r.height = Math.round(w/ratio);
		}
	} else {
		r.width = w;
		r.height = h;
	}
	return r;
}


function {$className}_makesize(value, is_height, uihandle)
{
	$('#{$className}_image').change();
	var pageunit_id = $(uihandle).parents('form').eq(0).attr('rel');
	if (is_height) {
		$('#cms-pageunit-'+pageunit_id).find('img').height(value);
		var w = $('#{$className}_width').val();
		var h = value;
	} else {
		$('#cms-pageunit-'+pageunit_id).find('img').width(value);		
		var w = value
		var h = $('#{$className}_height').val();
	}
	
	if ($('#{$className}_ratio').attr('checked')) {
		var im = new Image();
		im.src = $('#{$className}_image').val();
		var t = this;
		im.onload = function() {
		
			var size = {$className}_fixratio(w, h, this.width/this.height, is_height);
			if (is_height) {
				$('#cms-pageunit-'+pageunit_id).find('img').width(size.width);
				$('#{$className}_width').val(size.width);
				//$('#{$className}_width_slider').slider("option", "value", size.width);
				var wi = 100*size.width/2000;
				$('#{$className}_width_slider').find('.ui-slider-handle').eq(0).css('left', wi.toString() + '%');
				
			} else {
				$('#cms-pageunit-'+pageunit_id).find('img').height(size.height);
				$('#{$className}_height').val(size.height);
				//$('#{$className}_height_slider').slider("option", "value", size.height);
				var he = 100*size.height/2000;
				$('#{$className}_height_slider').find('.ui-slider-handle').eq(0).css('left', he.toString() + '%');
			}
			
		}
	}
	
	
}

//-->
</script>
EOD
,
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
				'<br /><hr />',
				'url'=>array(
					'type'=>'Link',
					'size'=>40,
					'showUploadButton'=>false
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