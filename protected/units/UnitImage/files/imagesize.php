<hr />
<select id="<?=$className;?>_setsize">
	<option value=""><?=Yii::t('UnitImage.unit', 'Select image size')?></option>
	<option value="0x0"><?=Yii::t('UnitImage.unit', 'Actual')?></option>
	<option value="88x31">88 x 31</option>
	<option value="100x100">100 x 100</option>
	<option value="150x400">150 x 400</option>
	<option value="200x200">200 x 200</option>
</select><br />
<input type="checkbox" id="<?=$className;?>_ratio" checked="checked" /><label style="display:inline;clear:none;" for="<?=$className;?>_ratio"> <?=Yii::t('UnitImage.unit', 'Aspect ratio')?></label>
<script type="text/javascript">
<!--
//$('.field_title').hide();

$('#<?=$className;?>_setsize').change(function() {

	$('#<?=$className;?>_image').change();
	var im = new Image();
	im.src = $('#<?=$className;?>_image').val();
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
				var s = <?=$className;?>_fixratio(w, h, this.width/this.height, ch);
				w = s.width;
				h = s.height;
			} else {
				w = this.width;
				h = this.height;
			}
			$('#<?=$className;?>_width_slider').slider( "option", "value", w);
			$('#<?=$className;?>_width').val(w);
			$('#<?=$className;?>_height_slider').slider( "option", "value", h);
			$('#<?=$className;?>_height').val(h);
		}

	}

});
$('#<?=$className;?>_setsize').keydown(function () {
	$('#<?=$className;?>_setsize').change();
});
$('#<?=$className;?>_ratio').click(function() {
	$('#<?=$className;?>_setsize').change();
});

function <?=$className;?>_fixratio(w, h, ratio, height_const)
{
	var r = {};
	if (height_const == null) {
		var height_const = ratio < 1;
	}
	if ($('#<?=$className;?>_ratio').attr('checked')) {
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


function <?=$className;?>_makesize(value, is_height, uihandle)
{
	$('#<?=$className;?>_image').change();
	var pageunit_id = $(uihandle).parents('form').eq(0).attr('rel');
	if (is_height) {
		$('#cms-pageunit-'+pageunit_id).find('img').height(value);
        $('#cms-pageunit-'+pageunit_id).find('img').parent('.ui-wrapper').height(value);
		var w = $('#<?=$className;?>_width').val();
		var h = value;
	} else {
		$('#cms-pageunit-'+pageunit_id).find('img').width(value);
        $('#cms-pageunit-'+pageunit_id).find('img').parent('.ui-wrapper').width(value);
		var w = value
		var h = $('#<?=$className;?>_height').val();
	}

	if ($('#<?=$className;?>_ratio').attr('checked')) {
		var im = new Image();
		im.src = $('#<?=$className;?>_image').val();
		var t = this;
		im.onload = function() {

			var size = <?=$className;?>_fixratio(w, h, this.width/this.height, is_height);
			if (is_height) {
				$('#cms-pageunit-'+pageunit_id).find('img').width(size.width);
                $('#cms-pageunit-'+pageunit_id).find('img').parent('.ui-wrapper').width(size.width);
				$('#<?=$className;?>_width').val(size.width);
				//$('#<?=$className;?>_width_slider').slider("option", "value", size.width);
				var wi = 100*size.width/2000;
				$('#<?=$className;?>_width_slider').find('.ui-slider-handle').eq(0).css('left', wi.toString() + '%');

			} else {
				$('#cms-pageunit-'+pageunit_id).find('img').height(size.height);
                $('#cms-pageunit-'+pageunit_id).find('img').parent('.ui-wrapper').height(size.height);
				$('#<?=$className;?>_height').val(size.height);
				//$('#<?=$className;?>_height_slider').slider("option", "value", size.height);
				var he = 100*size.height/2000;
				$('#<?=$className;?>_height_slider').find('.ui-slider-handle').eq(0).css('left', he.toString() + '%');
			}

		}
	}


}

//-->
</script>
