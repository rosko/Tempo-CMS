<?php

class Form extends CForm
{
    const TAB_DELIMETER = '##';
    const SECTION_DELIMETER = '====';
    private $_config = array();

	public function configure($config)
	{
		if(is_string($config))
			$config=require(Yii::getPathOfAlias($config).'.php');
		if(is_array($config))
		{
            $this->_config = $config;
			foreach($config as $name=>$value)
				$this->$name=$value;
		}
	}

	public function render()
	{
        $className = get_class($this->getModel(false));
        $js = <<<EOD
   <script type="text/javascript">
        $(function() {
EOD;
        if (is_subclass_of($className, 'I18nActiveRecord')) {
            $langs = array_keys(call_user_func(array($className, 'getLangs'), Yii::app()->language));
            if (!empty($langs)) {
                foreach ($this->_config['elements'] as $k => $v) {
                    if (in_array($k, call_user_func(array($className, 'i18n'))) && is_array($v)) {
                        $txtButton = Yii::t('languages', 'Translations');
                        $js .= <<<EOD
    //$('<br />').appendTo('#{$this->uniqueId} .field_{$k}');
    button = $('<span></span>').button({
        text: false,
        label: '{$txtButton}',
        icons: {
            primary: 'ui-icon-script'
        }
    }).appendTo('#{$this->uniqueId} .field_{$k}');
    fieldset = $('<fieldset></fieldset>')
        .attr('id', '{$this->uniqueId}_field_{$k}')
        .css('display', 'none')
        .appendTo('#{$this->uniqueId} .field_{$k}');
    button.click(function() {
        $('#{$this->uniqueId}_field_{$k}').slideToggle();
    });
    
EOD;
                        foreach ($langs as $lang) {
                            $this->getElements()->add($lang.'_'.$k, $v);
                            $js .= "$('#{$this->uniqueId} .field_{$lang}_{$k}').appendTo(fieldset);\n";
                        }
                    }
                }
            }
        }
        $js .= <<<EOD
        });
   </script>
EOD;
        return parent::render() . $js;
    }

    public function renderElements()
    {
        $output='';
        $js = <<<EOD
    <script type="text/javascript">
	$(function() {
EOD;
        $preoutput = '<ul>';
        $t_counter = 0;
        $s_counter = 0;
        $i=0;
        $td_len = strlen(self::TAB_DELIMETER);
        $sd_len = strlen(self::SECTION_DELIMETER);
        foreach($this->getElements() as $element) {
            $i++;
            if (get_class($element) == 'CFormStringElement' && is_string($element->content) &&
                substr($element->content,0,$td_len)==self::TAB_DELIMETER
                    && substr($element->content,-$td_len)==self::TAB_DELIMETER  ){
                if ($i > 1) {
                    $title = Yii::t('cms', 'Properties');
                    $link = '#cms-form-'.$this->uniqueId.'-tab-'.$t_counter;
                    $output .= '</div><div id="cms-form-'.$this->uniqueId.'-tab-'.$t_counter.'">';
                    $t_counter++;
                    $preoutput .= '<li><a href="'.$link.'">'.$title.'</a></li>';

                }
                break;
            }
        }

        foreach($this->getElements() as $element) {
            if (get_class($element) == 'CFormStringElement' && is_string($element->content)) {
                if (substr($element->content,0,$td_len)==self::TAB_DELIMETER
                    && substr($element->content,-$td_len)==self::TAB_DELIMETER  ){
                    if ($s_counter) {
                        $output .= '</div></div>';
                    }
                    $text = substr($element->content,$td_len,-$td_len);
                    $tmp = explode(Form::TAB_DELIMETER, $text);
                    $title = $tmp[0];
                    if (isset($tmp[1])&&($tmp[1] != '')) {
                        $link = $tmp[1];
                    } else {
                        $link = '#cms-form-'.$this->uniqueId.'-tab-'.$t_counter;
                        $output .= '</div><div id="cms-form-'.$this->uniqueId.'-tab-'.$t_counter.'">';
                    }
                    $t_counter++;
                    $s_counter = 0;
                    $preoutput .= '<li><a href="'.$link.'">'.$title.'</a></li>';
                } elseif (substr($element->content,0,$sd_len)==Form::SECTION_DELIMETER
                    && substr($element->content,-$sd_len)==Form::SECTION_DELIMETER) {
                    $title = substr($element->content,$sd_len,-$sd_len);
                    
                    if ($s_counter) {
                        $output .= '</div>';
                    } else {
                        $output .= '<div id="cms-form-'.$this->uniqueId.'-sections-'.$t_counter.'">';
                        $js .= <<<EOD
   $('#cms-form-{$this->uniqueId}-sections-{$t_counter}').accordion({
       autoHeight:false,
       collapsible:true
   });
EOD;
                    }
                    $output .= '<h3><a href="#">'.$title.'</a></h3><div>';
                    $s_counter++;


                } else $output.=$this->renderElement($element);
            } else $output.=$this->renderElement($element);
        }
        if ($s_counter) {
            $output .= '</div>';
        }
        if ($t_counter > 0) {
            $preoutput .= '</ul>';
            $output = substr($output,6) . '</div>';
            $txtLoadError = Yii::t('cms', 'Tab loading error');
            $js .= <<<EOD
		$("#cms-form-tabs-{$this->uniqueId}").tabs({
            collapsible: true,
			ajaxOptions: {
				error: function( xhr, status, index, anchor ) {
					$( anchor.hash ).html( "{$txtLoadError}" );
				}
			}
        });
   	});
    </script>
EOD;
            return '<div id="cms-form-tabs-'.$this->uniqueId.'">' . $preoutput . $output . '</div>' . $js;
        } elseif ($s_counter >0) {
            $output .= '</div>';
            $js .= <<<EOD
   	});
    </script>
EOD;
            return $output . $js;

        } else {
            return $output;
        }
    }

    public static function tab($title, $link='')
    {
        $output = Form::TAB_DELIMETER . $title . Form::TAB_DELIMETER;
        if ($link !='') {
            $output .= $link . Form::TAB_DELIMETER;
        }
        return $output;
    }

    public static function section($title)
    {
        $output = Form::SECTION_DELIMETER . $title . Form::SECTION_DELIMETER;
        return $output;
    }

    public static function ajaxify($id)
    {
        return array(
            'class' => 'CActiveForm',
            'enableAjaxValidation' => true,
            'id' => $id,
            'focus' => 'input[type="text"]',
            'clientOptions'=>array(
                'ajaxVar'=>'ajax-validate',
                'validateOnSubmit'=>true,
                'validateOnChange'=>true,
                'validateOnType'=>false,
                'afterValidate'=> 'js:function(f,d,h){ajaxSubmitForm(f,d,h);}'
            ),
        );
    }

}
