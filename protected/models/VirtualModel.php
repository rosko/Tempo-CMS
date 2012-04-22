<?php
/**
 * return array(
 * 	'site_title'=>array(
 * 		'type'=>'text',
 * 		'maxlength'=>80,
 * 		'label'=>'Open user register',
 *      'default'=>'Yii Site',
 *     	'rules'=>array(
 *         array('site_title', 'length', 'max'=>64),
 *     )
 * 	),
 *  ...
 * );
 */
class VirtualModel extends CModel
{
    private $_attributes = array();
    private $_rules = array();
    private $_labels = array();
    private $_values = array();

    public function __construct($config = null, $format = 'native', $default=array())
    {
        if (!is_null($config)) {
            if ($format == 'FieldSet') {
                $_inputConfig = $config;
                $inputConfig = array();
                $config = array();
                foreach ($_inputConfig as $key => $field) {
                    if (isset($field['i18n']) && $field['i18n']) {
                        unset($field['i18n']);
                        $langs = I18nActiveRecord::getLangs();
                        foreach ($langs as $langId => $langTitle) {
                            $_field = $field;
                            if (is_string($_field['label']) && $_field['label'])
                                $_field['label'] .= ' [' . Yii::t('languages', $langTitle) . ']';
                            if (is_string($_field['hint']) && $_field['hint'])
                                    $_field['hint'] .= ' [' . Yii::t('languages', $langTitle) . ']';
                            $_field['name'] = $langId . '_' . $_field['name'];
                            $inputConfig[] = $_field;
                        }
                    } else {
                        $inputConfig[] = $field;
                    }
                }

                foreach ($inputConfig as $key => $field) {

                    $name = $field['name'];
                    unset($field['name']);
                    if (isset($default[$name]))
                        $field['default'] = $default[$name];

                    if (is_array($field['label']))
                        $field['label'] = $field['label'][Yii::app()->language];
                    if (is_array($field['hint']))
                        $field['hint'] = $field['hint'][Yii::app()->language];

                    if (isset($field['rules'])) foreach ($field['rules'] as $i => $rule) {
                        array_unshift($rule, $name);
                        $field['rules'][$i] = $rule;
                    }
                    if (empty($field['rules'])) $field['rules'] = array(array($name, 'safe'));

                    $config[$name] = $field;
                }
            }
            $this->configure($config);
        }
    }

    public function attributeNames()
    {
        return array_keys($this->_attributes);
    }

    public function attributeLabels()
    {
    	return $this->_labels;
    }

    public function rules()
    {
        return $this->_rules;
    }

    protected function configure($config)
    {
        if (is_string($config)) {
            $config = Yii::getPathOfAlias($config).'.php';
            $config = require($config);
        }
        foreach ($config as $name => $attribute) {
            if (is_array($attribute))
                $this->addAttribute($name, $attribute);
        }
    }

    public function __set($name, $value)
    {
    	if (isset($this->_attributes[$name]))
    	    $this->_values[$name] = $value;
    	else
    	    parent::__set($name, $value);
    }

    public function __get($name)
    {
        if (isset($this->_attributes[$name]))
            return isset($this->_values[$name])?$this->_values[$name]:'';
    	else
    	    return parent::__get($name);
    }

    public function addAttribute($name, $attribute)
    {
    	if (isset($attribute['rules'])) {
    	    $this->_rules = CMap::mergeArray($this->_rules, $attribute['rules']);
    	    unset($attribute['rules']);
    	}else{
    	    $this->_rules[] = array($name, 'safe');
    	}
    	if (isset($attribute['label'])) {
    	    $this->_labels[$name] = $attribute['label'];
    	    //unset($attribute['label']);
    	}
        if (isset($attribute['default'])) {
    	    $this->_values[$name] = $attribute['default'];
    	    unset($attribute['default']);
    	}
    	$this->_attributes[$name] = $attribute;
    }

    public function removeAttribute($name)
    {
    	unset($this->_labels[$name], $this->_values[$name], $this->_rules[$name]);
    }

    public function setValue($name, $value)
    {
    	$this->_values[$name] = $value;
    }

    public function getValue($name)
    {
    	return $this->_values[$name];
    }

    public function getFormMap()
    {
        return empty($this->_attributes)?array():array('elements'=>$this->_attributes);
    }
}