<hr />
<select id="<?=$className;?>_setsize">
	<option value=""><?=Yii::t('UnitVideo.main', 'Select size')?></option>
<?php
    foreach ($sizes as $k => $v) {
    	?><option value="<?=$k?>"><?=$v?></option><?php
    }
?>
</select><br />
<script type="text/javascript">
<!--
//$('.field_title').hide();

$('#<?=$className;?>_setsize').change(function() {

	$('#<?=$className;?>_<?=$attribute?>').change();
    var w = $(this).val().substring(0, $(this).val().indexOf('x'));
    var h = $(this).val().substring($(this).val().indexOf('x')+1);
    $('#<?=$className;?>_<?=$width?>_slider').slider( "option", "value", w);
    $('#<?=$className;?>_<?=$width?>').val(w);
    $('#<?=$className;?>_<?=$height?>_slider').slider( "option", "value", h);
    $('#<?=$className;?>_<?=$height?>').val(h);


});
$('#<?=$className;?>_setsize').keydown(function () {
	$('#<?=$className;?>_setsize').change();
});


function <?=$className;?>_makesize(value, is_height, uihandle)
{
	$('#<?=$className;?>_<?=$attribute?>').change();
	var pageUnitId = $(uihandle).parents('form').eq(0).attr('rel');
	if (is_height) {
		$('#cms-pageunit-'+pageUnitId).find('<?=$selector?>').height(value);
		var w = $('#<?=$className;?>_<?=$width?>').val();
		var h = value;
	} else {
		$('#cms-pageunit-'+pageUnitId).find('<?=$selector?>').width(value);
		var w = value
		var h = $('#<?=$className;?>_<?=$height?>').val();
	}

}

//-->
</script>
