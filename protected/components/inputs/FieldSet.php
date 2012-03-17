<?php

class FieldSet extends CInputWidget
{
    public $allowTypes = array(
        'text',
        'password',
        'textarea',
        'file',
        'radio',
        'checkbox',
        'Select',
/*        'listbox',
        'dropdownlist',
        'checkboxlist',
        'radiolist',*/
        'ComboBox',
        'DatePicker',
        'DateTimePicker',
        'Link',
        'Slider',
        'VisualTextAreaFCK',
        'Captcha',
    );

    // Обязательные атрибуты для полей
    public $attributes = array(
        'listbox'=>array(
            'items'=>array(
                'type'=>'ListEdit',
                'i18n'=>false,
            ),
        ),
        'dropdownlist'=>array(
            'items'=>array(
                'type'=>'ListEdit',
                'i18n'=>false,
            ),
        ),
        'checkboxlist'=>array(
            'items'=>array(
                'type'=>'ListEdit',
                'i18n'=>false,
            ),
        ),
        'radiolist'=>array(
            'items'=>array(
                'type'=>'ListEdit',
                'i18n'=>false,
            ),
        ),
        'Select'=>array(
            'options'=>array(
                'type'=>'ListEdit',
                'i18n'=>true,
            ),
            'input'=>array(
                'type'=>'dropdownlist',
                'items'=>array(
                    'dropdownlist'=>'dropdownlist',
                    'listbox'=>'listbox',
                    'checkboxlist'=>'checkboxlist',
                    'radiolist'=>'radiolist',
                ),
            ),
        ),
        'textarea'=>array(
            'rows'=>array(
                'type'=>'Slider',
            ),
            'cols'=>array(
                'type'=>'Slider',
            ),
        ),
    );

    // Обязательные правила для полей
    public $rules = array(
        '*'=>array('required'),
        'text'=>array('email', 'length'),
    );

    // Правила, которые можно подключать дополнительно
    public $extraRules = array(
        'text'=>array('filter', 'match', 'type', 'default', 'url'),
        'file'=>array('file'),
        'Captcha'=>array('captcha'),
        'Slider'=>array('numerical'),
        'checkbox'=>array('boolean'),
    );


    public $validators = array(
        'required'=>array(),
        'filter'=>array(
            'filter'=>array(
                'type'=>'dropdownlist',
                'items'=>array(
                    'trim'=>'trim',
                    'strtolower'=>'strtolower',
                    'strtoupper'=>'strtoupper',
                    'ucfirst'=>'ucfirst',
                    'ucwords'=>'ucwords',
                ),
            ),
        ),
        'match'=>array(
            'pattern'=>array(
                'type'=>'text',
            ),
            'not'=>array(
                'type'=>'checkbox',
            ),
        ),
        'email'=>array(
            'checkMX'=>array(
                'type'=>'checkbox',
            ),
        ),
        'url'=>array(),
        'unique'=>array(
            'attributeName'=>array(
                'type'=>'text',
            ),
            'className'=>array(
                'type'=>'text',
            ),
            'caseSensetive'=>array(
                'type'=>'checkbox',
            ),
        ),
        'compare'=>array(
            'compareAttributes'=>array(
                'type'=>'text',
            ),
            'operator'=>array(
                'type'=>'dropdownlist',
                'items'=>array(
                    '==',
                    '!-',
                    '>',
                    '>=',
                    '<',
                    '<=',
                ),
            ),
        ),
        'length'=>array(
            'min'=>array(
                'type'=>'text',
            ),
            'max'=>array(
                'type'=>'text',
            ),
            'is'=>array(
                'type'=>'text',
            ),
            'encoding'=>'UTF-8',
        ),
        'numerical'=>array(
            'integerOnly'=>array(
                'type'=>'checkbox',
            ),
            'min'=>array(
                'type'=>'text',
            ),
            'max'=>array(
                'type'=>'text',
            ),
        ),
        'captcha'=>array(
            'captchaAction'=>array(
                'type'=>'hidden',
                'value'=>'site/captcha',
            ),
        ),
        'type'=>array(
            'type'=>array(
                'type'=>'dropdownlist',
                'items'=>array(
                    'integer',
                    'float',
                    'string',
                    'date',
                    'time',
                    'datatime',
                ),
            ),
            'dateFormat'=>array(
                'type'=>'text',
            ),
            'datetimeFormat'=>array(
                'type'=>'text',
            ),
            'timeFormat'=>array(
                'type'=>'text',
            ),
        ),
        'file'=>array(
            'maxFiles'=>array(
                'type'=>'text',
            ),
            'maxSize'=>array(
                'type'=>'text',
            ),
            'minSize'=>array(
                'type'=>'text',
            ),
            'types'=>array(
                'type'=>'text',
            ),
        ),
        'default'=>array(
            'value'=>array(
                'type'=>'text',
            ),
        ),
        'exist'=>array(
            'attributeName'=>array(
                'type'=>'text',
            ),
            'className'=>array(
                'type'=>'text',
            ),
        ),
        'boolean'=>array(
            'falseValue'=>array(
                'type'=>'text',
            ),
            'trueValue'=>array(
                'type'=>'text',
            ),
        ),
        
    );

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

        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;
        if (!is_array($value)) {
            $unser = unserialize($value);
            $value =  $unser===FALSE ? $value : $unser;
        }

        $langs = I18nActiveRecord::getLangs(Yii::app()->language);

        $this->render('FieldSet', array(
            'id' => $id,
            'name' => $name,
            'fields' => $value,
            'langs' => $langs,
        ));
    }

    public function typesLabels()
    {
        return array(
            'text'=>Yii::t('cms', 'Text field'),
            'textarea'=>Yii::t('cms', 'Multiline text field'),
            'password'=>Yii::t('cms', 'Password field'),
            'file'=>Yii::t('cms', 'File upload field'),
            'radio'=>Yii::t('cms', 'Radio field'),
            'checkbox'=>Yii::t('cms', 'Checkbox field'),
            'listbox'=>Yii::t('cms', 'Listbox field'),
            'dropdownlist'=>Yii::t('cms', 'Dropdownlist field'),
            'checkboxlist'=>Yii::t('cms', 'Checkboxlist field'),
            'radiolist'=>Yii::t('cms', 'Radiolist field'),
            'Select'=>Yii::t('cms', 'Select field'),
            'ComboBox'=>Yii::t('cms', 'Combobox field'),
            'DatePicker'=>Yii::t('cms', 'Date picker'),
            'DateTimePicker'=>Yii::t('cms', 'Date and time picker'),
            'Link'=>Yii::t('cms', 'Link field'),
            'Slider'=>Yii::t('cms', 'Slider field'),
            'VisualTextAreaFCK'=>Yii::t('cms', 'Text editor'),
            'Captcha'=>Yii::t('cms', 'Captcha'),
        );
    }

    public function getTypeRules($type) {
        $a = isset($this->rules['*']) ? $this->rules['*'] : array();
        $b = isset($this->rules[$type]) ? $this->rules[$type] : array();
        $c = isset($this->extraRules['*']) ? $this->extraRules['*'] : array();
        $d = isset($this->extraRules[$type]) ? $this->extraRules[$type] : array();
        return array_merge(array_merge($a, $b), array_merge($c, $d));
    }

}
