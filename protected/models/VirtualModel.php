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

    public function __construct($config = null)
    {
        if (!is_null($config)) {
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