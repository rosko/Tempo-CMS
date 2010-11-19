<?php

Yii::import('zii.widgets.jui.CJuiInputWidget');

class ComboBox extends CJuiInputWidget
{
    public $array = array();
    public $empty = '«нет»';

    public function init()
    {
        $this->themeUrl = Yii::app()->params->jui['themeUrl'];
        $this->theme = Yii::app()->params->jui['theme'];
        parent::init();
    }

    public function run()
	{
        $this->array = array_merge(array('0'=>$this->empty), $this->array);

        list($name,$id)=$this->resolveNameID();

		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;

		if(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];

		if($this->hasModel())
			echo CHtml::activeDropDownList($this->model,$this->attribute,$this->array, $this->htmlOptions);
		else
			echo CHtml::dropDownList($name,$this->value,$this->array,$this->htmlOptions);

        $this->options['focus'] = <<<EOD
js:function(event, ui) {
    $('#{$id}').val(ui.item.label);
    return false;
}
EOD;
        $this->options['select'] = <<<EOD
js:function(event, ui) {
    $('#{$id}').val(ui.item.label);
    $('#{$original_id}').val(ui.item.value);
    return false;
}
EOD;

        $options=CJavaScript::encode($this->options);

        $js = <<<EOD

	(function( $ ) {
		$.widget( "ui.combobox", {
			_create: function() {
				var self = this,
					select = this.element.hide(),
					selected = select.children( ":selected" ),
					value = selected.val() ? selected.text() : "";
				var input = $( "<input>" )
					.insertAfter( select )
					.val( value )
					.autocomplete({
						delay: 0,
						minLength: 0,
						source: function( request, response ) {
							var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
							response( select.children( "option" ).map(function() {
								var text = $( this ).text();
								if ( this.value && ( !request.term || matcher.test(text) ) )
									return {
										label: text.replace(
											new RegExp(
												"(?![^&;]+;)(?!<[^<>]*)(" +
												$.ui.autocomplete.escapeRegex(request.term) +
												")(?![^<>]*>)(?![^&;]+;)", "gi"
											), "<strong>$1</strong>" ),
										value: text,
										option: this
									};
							}) );
						},
						select: function( event, ui ) {
							ui.item.option.selected = true;
							self._trigger( "selected", event, {
								item: ui.item.option
							});
						},
						change: function( event, ui ) {
							if ( !ui.item ) {
								var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
									valid = false;
								select.children( "option" ).each(function() {
									if ( this.value.match( matcher ) ) {
										this.selected = valid = true;
										return false;
									}
								});
								if ( !valid ) {
									// remove invalid value, as it didn't match anything
									$( this ).val( "" );
									select.val( "" );
									return false;
								}
							}
						}
					})
					.addClass( "ui-widget ui-widget-content ui-corner-left" );

				input.data( "autocomplete" )._renderItem = function( ul, item ) {
					return $( "<li></li>" )
						.data( "item.autocomplete", item )
						.append( "<a>" + item.label + "</a>" )
						.appendTo( ul );
				};

				$( "<a>&nbsp;</a>" )
					.attr( "tabIndex", -1 )
					.attr( "title", "Show All Items" )
					.insertAfter( input )
					.button({
						icons: {
							primary: "ui-icon-triangle-1-s"
						},
						text: false
					})
					.removeClass( "ui-corner-all" )
					.addClass( "ui-corner-right ui-button-icon" )
					.click(function() {
						// close if already visible
						if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
							input.autocomplete( "close" );
							return false;
						}

						// pass empty string as value to search for, displaying all results
						input.autocomplete( "search", "" );
						input.focus();
                        return false;
					});
			}
		});
	})( jQuery );
EOD;

		$js .= "$('#{$id}').combobox();";
        $cs = Yii::app()->getClientScript();
		$cs->registerScript(__CLASS__.'#'.$id, $js);

    }

}

?>