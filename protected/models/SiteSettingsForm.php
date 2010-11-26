<?php

class SiteSettingsForm extends CFormModel
{
	private $_attributes=array();

    public function rules()
    {
        // Правила для проверки общих настроек
        $ret = array(
			array('sitename, adminEmail, defaultsPerPage', 'required'),
            array('sitename', 'length', 'max'=>100),
            array('adminEmail', 'email'),
            array('defaultsPerPage', 'numerical', 'min'=>1, 'integerOnly'=>true),
			array('simpleMode, autoSave, showUnitAppearance', 'boolean'),
            array('theme', 'length', 'max'=>100),
        );
        // Правила для проверки настроек для юнитов
        $unit_types = Unit::getTypes();
        foreach ($unit_types as $unit_class) {
            if (method_exists($unit_class, 'settingsRules')) {
                $rules = $unit_class::settingsRules();
                if (is_array($rules) && !empty($rules)) {
                    foreach ($rules as $rule)
                    {
                        $params = explode(',',str_replace(' ', '', $rule[0]));
                        foreach ($params as $k => $v) {
                            $params[$k] = $unit_class . '.' . $v;
                        }
                        $rule[0] = implode(',',$params);
                        $ret[] = $rule;
                    }
                }
            }
        }
        return $ret;
    }
    
	public function attributeLabels()
	{
		return array(
			'sitename' => 'Название сайта',
            'adminEmail' => 'E-mail администратора',
            'defaultsPerPage' => 'Количество объектов на одной странице, по-умолчанию',
			'simpleMode' => 'Упрощенный режим управления сайтом',
            'autoSave' => 'Автосохранение при редактировании (каждые 30 секунд)',
            'showUnitAppearance' => 'Отображать для блоков закладку "Внешний вид"',
            'theme' => 'Тема оформления (дизайн) сайта',
		);
	}

    public function form()
    {
        // Общие настроки
        $ret = array(
            'elements'=>array(
                Form::tab('Общие настройки'),
                'sitename'=>array(
                    'type'=>'text',
                    'size'=>60
                ),
                'adminEmail'=>array(
                    'type'=>'text',
                    'size'=>60
                ),
//				'simpleMode'=>array(
//					'type'=>'checkbox',
//				),
                'autoSave'=>array(
                    'type'=>'checkbox'
                ),
                Form::tab('Внешний вид'),
                'theme'=>array(
                    'type'=>'ThemeSelect',
                    'empty'=>null,
                ),
                'defaultsPerPage'=>array(
                    'type'=>'Slider',
					'options'=>array(
						'min'=>1,
						'max'=>50
					)
                ),
                'showUnitAppearance'=>array(
                    'type'=>'checkbox'
                ),
            ),
        );
        // Настройки для юнитов
        $unit_types = Unit::getTypes();
        $ret['elements'][] = Form::tab('Настройки блоков');
        foreach ($unit_types as $unit_class) {
            if (method_exists($unit_class, 'settings')) {
                $elems = $unit_class::settings($unit_class);
                if (is_array($elems) && !empty($elems)) {
                    $ret['elements'][] = Form::section($unit_class::NAME);
                    foreach ($elems as $k => $elem)
                    {
                        $ret['elements'][$unit_class.'.'.$k] = $elem;
                    }
                }
            }
        }
        return $ret;
    }


	public function __get($name)
	{
		if(isset($this->_attributes[$name]))
            return $this->_attributes[$name];
	}

	public function __set($name,$value)
	{
		$this->setAttribute($name,$value);
	}

	public function __isset($name)
	{
		return isset($this->_attributes[$name]);
	}

	public function __unset($name)
	{
		if(isset($this->_attributes[$name]))
			unset($this->_attributes[$name]);
	}
	
	public function setAttribute($name,$value)
	{
		if(property_exists($this,$name))
    		$this->$name=$value;
		else
			$this->_attributes[$name]=$value;
		return true;
	}
	
	public function getAttributes()
	{
		return $this->_attributes;
	}
	
	public function validate()
	{
		return parent::validate(array_keys($this->_attributes));
	}
    
}