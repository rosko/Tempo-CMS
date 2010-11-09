<?php

class Form extends CForm
{
    const TAB_DELIMETER = '##';

    public function renderElements()
    {
        $output='';
        $js = <<<EOD
    <script type="text/javascript">
	$(function() {
		$("#cms-form-tabs-{$this->uniqueId}").tabs({
            collapsible: true,
			ajaxOptions: {
				error: function( xhr, status, index, anchor ) {
					$( anchor.hash ).html( "Ошибка при загрузке закладки." );
				}
			}
        });
	});
	</script>
EOD;
        $preoutput = '<ul>';
        $counter = 0;
        foreach($this->getElements() as $element) {
            if (get_class($element) == 'CFormStringElement' && is_string($element->content) &&
                substr($element->content,0,2)==Form::TAB_DELIMETER
                    && substr($element->content,-2)==Form::TAB_DELIMETER  ){
                $text = substr($element->content,2,-2);
                $tmp = explode(Form::TAB_DELIMETER, $text);
                $title = $tmp[0];
                if (isset($tmp[1])&&($tmp[1] != '')) {
                    $link = $tmp[1];
                } else {
                    $link = '#cms-form-'.$this->uniqueId.'-tab-'.$counter;
                    $output .= '</div><div id="cms-form-'.$this->uniqueId.'-tab-'.$counter.'">';
                }
                $counter++;
                $preoutput .= '<li><a href="'.$link.'">'.$title.'</a></li>';
            } else $output.=$this->renderElement($element);
        }
        if ($counter > 0) {
            $preoutput .= '</ul>';
            $output = substr($output,6) . '</div>';
            return '<div id="cms-form-tabs-'.$this->uniqueId.'">' . $preoutput . $output . '</div>' . $js;
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

    public static function ajaxify($id)
    {
        return array(
            'class' => 'CActiveForm',
            'enableAjaxValidation' => true,
            'id' => $id,
// from Yii 1.1.4                'focus' => 'input[type="text"]',
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
