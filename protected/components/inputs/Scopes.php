<?php

class Scopes extends CInputWidget
{
    public $className;
    public $classNameAttribute;

    public function run()
    {

        list($name,$id)=$this->resolveNameID();
        if(isset($this->htmlOptions['id']))
            $id=$this->htmlOptions['id'];
        else
            $this->htmlOptions['id']=$id;
        if(isset($this->htmlOptions['name']))
            $name=$this->htmlOptions['name'];
        else
            $this->htmlOptions['name']=$name;

        if (isset($this->size))
            $this->htmlOptions['size'] = $this->size;

        if($this->hasModel())
            echo CHtml::activeHiddenField($this->model,$this->attribute,$this->htmlOptions);
        else
            echo CHtml::hiddenField($name,$this->value,$this->htmlOptions);

        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;
        //$value = unserialize($value);
        $tmp = explode('->', $value);
        $value = array();
        foreach ($tmp as $t)
        {
            $pos = strpos($t, '(');
            $n = substr($t,0,$pos);
            $p = substr($t,$pos+1,-1);
            $value[$n][] = true;
            if ($p) {
                $params = explode(',',$p);
                foreach ($params as $k => $v)
                {
                    $value[$n][$k] = $v;
                }
            }
        }

        if (!$this->className) {
            !$this->className = $this->model->{$this->classNameAttribute};
        }
        $className = $this->className;
        if (!Yii::$classMap[$className] || !method_exists($className, 'scopesLabels') || !method_exists($className, 'hiddenScopes') || !method_exists($className, 'scopes')) return;

        $labels = call_user_func(array($className, 'scopesLabels'));
        $hidden = call_user_func(array($className, 'hiddenScopes'));

        foreach (call_user_func(array($className, 'scopes')) as $k => $scope)
        {
            if (in_array($k, $hidden)) continue;
            $_id = __CLASS__ . '_' . $id . '_' . $k;
            echo '<div class="row">';
            echo CHtml::label($labels[$k], $_id);
            echo CHtml::checkBox($k, $value[$k][0], array('id' => $_id, 'rev' => $k, 'class' => $id . '-scope'));
            echo '</div>';
        }
        foreach (call_user_func(array($className, 'namedScopes')) as $k => $scope)
        {
            if (in_array($k, $hidden)) continue;
            $i=0;
            $_id = __CLASS__ . '_' . $id . '_' . $k;
            echo '<div class="row">';
            echo CHtml::label($labels[$k][0], $_id);
            echo CHtml::checkBox($k, $value[$k][0], array('id' => $_id, 'rev' => $k, 'class' => $id . '-scope',
                'onclick' => "js:$('#{$id}_{$k}_div').slideToggle();"
                 ));
            echo "<fieldset class='{$id}_fieldset' id='{$id}_{$k}_div' style='display:".($value[$k][0]?'block':'none').";'>";
            foreach ($scope as $field => $input)
            {
                $attributes = array_merge(array(
                    'id' => $_id . '_' . $field,
                    'htmlOptions' => array(
                        'class' => $id . '-scope ' . $_id . '-field',
                        'onclick' => "js:$('#{$id}').val(generateChain());",
                        'onchange' => "js:$('#{$id}').val(generateChain());",
                     )
                ), $input);
                unset($attributes['type']);
                echo CHtml::label($labels[$k][$field], $attributes['id']);
                echo $this->renderElement($input['type'], $field, $value[$k][$i], $attributes);
                $i++;
            }
            echo '</fieldset>';
            echo '</div>';
        }

        $this->registerClientScript();

    }

    public function registerClientScript()
    {
        if (!$this->className) {
            !$this->className = $this->model->{$this->classNameAttribute};
        }
        $className = $this->className;
        if (!Yii::$classMap[$className]) return;
        $id=$this->htmlOptions['id'];
        $js = "var scopes = {};\n";
        foreach (call_user_func(array($className, 'namedScopes')) as $k => $scope)
        {
            $fields = array_keys($scope);
            $js .= "scopes['{$k}'] = {" . implode(":true, ",$fields) . ":true};\n";
        }
        $js .= <<<EOD

function generateChain()
{
    var ret = '';
    var k = '';
    var n = '';
    var chains = new Array();
    $('.{$id}-scope:checkbox').each(function() {
        if ($(this).attr('checked')) {
            obj = $(this);
            k = obj.attr('rev');
            params = new Array();
            $('#{$id}_'+k+'_div').find('.{$id}-scope').each(function() {
                ret += 'd';
                n = $(this).attr('name');
                if (scopes[k][n]) {
                    params.push($(this).val());
                }
            });
            chains.push(k + '(' + params.join(',') + ')');
        }
    });
    ret = chains.join('->');
    return ret;
}

$('.{$id}-scope, .{$id}_fieldset input').bind('click focusin focusout keydown change', function() {
    $('#{$id}').val(generateChain());
});
EOD
;

        $cs=Yii::app()->getClientScript();
        $cs->registerScript('Yii.Inputs.Scopes#'.$id,$js, CClientScript::POS_READY);

    }

    protected function renderElement($type, $name, $value, $attributes)
    {
		if(isset(CFormInputElement::$coreTypes[$type]))
		{
            $htmlOptions = $attributes['htmlOptions'];
            unset($attributes['htmlOptions']);
            $attibutes = array_merge($attributes, $htmlOptions);
			$method=CFormInputElement::$coreTypes[$type];
            if (substr($method,0,6)=='active') {
                $method = lcfirst(substr($method,6));
            }
			if(strpos($method,'List')!==false) {
                $items = array();
                if (isset($attributes['items'])) {
                    $items = $attributes['items'];
                }
				return CHtml::$method($name, $value, $items, $attributes);
            }
			else
				return CHtml::$method($name, $value, $attributes);
		}
		else 
        {
            $attributes['value'] = $value;
            $attributes['name'] = $name;
			return $this->widget($type, $attributes, true);
        }
    }
}
