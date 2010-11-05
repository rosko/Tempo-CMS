<?php

class SiteSettingsForm extends CFormModel
{
	private $_attributes=array();

    public function rules()
    {
        return array(
			array('sitename, adminEmail, defaultsPerPage', 'required'),
            array('sitename', 'length', 'max'=>100),
            array('adminEmail', 'email'),
            array('defaultsPerPage', 'numerical', 'min'=>1, 'integerOnly'=>true),
			array('areaTopThrough, areaRightThrough, areaMainThrough', 'boolean'),
			array('simpleMode', 'boolean')
        );
    }
    
	public function attributeLabels()
	{
		return array(
			'sitename' => 'Название сайта',
            'adminEmail' => 'E-mail администратора',
            'defaultsPerPage' => 'Количество объектов на одной странице, по-умолчанию',
			'areaTopThrough' => 'Верхняя область для блоков является сквозной',
			'areaRightThrough' => 'Правая область для блоков является сквозной',
			'areaMainThrough' => 'Центральная область является сквозной',
			'simpleMode' => 'Упрощенный режим управления сайтом',
		);
	}

    public function form()
    {
        return array(
            'elements'=>array(
                'sitename'=>array(
                    'type'=>'text',
                    'size'=>60
                ),
                'adminEmail'=>array(
                    'type'=>'text',
                    'size'=>60
                ),
                'defaultsPerPage'=>array(
                    'type'=>'Slider',
					'options'=>array(
						'min'=>1,
						'max'=>50
					)
                ),
				'areaTopThrough'=>array(
					'type'=>'checkbox',
				),
				'areaRightThrough'=>array(
					'type'=>'checkbox',
				),
				'areaMainThrough'=>array(
					'type'=>'checkbox',
				),
				'simpleMode'=>array(
					'type'=>'checkbox',
				),
            ),
        );
    }


	public function __get($name)
	{
		if(isset($this->_attributes[$name]))
				return $this->_attributes[$name];
		else
				return parent::__get($name);
	}

	public function __set($name,$value)
	{
		if($this->setAttribute($name,$value)===false)
		{
			parent::__set($name,$value);
		}
	}

	public function __isset($name)
	{
		if(isset($this->_attributes[$name]))
			return true;
		else
			return parent::__isset($name);
	}

	public function __unset($name)
	{
		if(isset($this->_attributes[$name]))
			unset($this->_attributes[$name]);
		else
			parent::__unset($name);
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