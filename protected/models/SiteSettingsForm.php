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
			array('simpleMode, autoSave', 'boolean')
        );
    }
    
	public function attributeLabels()
	{
		return array(
			'sitename' => 'Название сайта',
            'adminEmail' => 'E-mail администратора',
            'defaultsPerPage' => 'Количество объектов на одной странице, по-умолчанию',
			'simpleMode' => 'Упрощенный режим управления сайтом',
            'autoSave' => 'Автосохранение при редактировании',
		);
	}

    public function form()
    {
        return array(
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
                'defaultsPerPage'=>array(
                    'type'=>'Slider',
					'options'=>array(
						'min'=>1,
						'max'=>50
					)
                ),
				'simpleMode'=>array(
					'type'=>'checkbox',
				),
                'autoSave'=>array(
                    'type'=>'checkbox'
                )
            ),
        );
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