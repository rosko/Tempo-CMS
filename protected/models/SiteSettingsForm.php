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
            array('cacheTime', 'numerical', 'min'=>0, 'integerOnly'=>true),
			array('simpleMode, autoSave, showUnitAppearance, ajaxPager, ajaxPagerScroll', 'boolean'),
            array('theme', 'length', 'max'=>100),
            array('language', 'length', 'max'=>10),
            array('defaultsShowEmail, defaultsSendMessage', 'length', 'max'=>32),
            array('userExtraFields', 'safe'),
            array('slugTransliterate, slugLowercase', 'boolean'),
            array('timezone', 'safe'),
        );
        // Правила для проверки настроек для юнитов
        $units = ContentUnit::getInstalledUnits();
        foreach ($units as $unitClass) {
            if (method_exists($unitClass, 'settingsRules')) {
                $rules = call_user_func(array($unitClass, 'settingsRules'));
                if (is_array($rules) && !empty($rules)) {
                    foreach ($rules as $rule)
                    {
                        $params = explode(',',str_replace(' ', '', $rule[0]));
                        foreach ($params as $k => $v) {
                            $params[$k] = $unitClass . '.' . $v;
                        }
                        $rule[0] = implode(',',$params);
                        $ret[] = $rule;
                    }
                }
            }
        }
        return self::localizedRules($ret);
    }
    
    public function attributeLabels()
	{
		$ret = array(
			'sitename' => Yii::t('cms', 'Sitename'),
            'adminEmail' => Yii::t('cms', 'Web-master\'s e-mail'),
            'defaultsPerPage' => Yii::t('cms', 'Entries per page, by default'),
			'simpleMode' => Yii::t('cms', 'Simple edit mode'),
            'autoSave' => Yii::t('cms', 'Autosaving on editing (every 30 seconds)'),
            'showUnitAppearance' => Yii::t('cms', 'Show "Appearance" tab for units'),
            'theme' => Yii::t('cms', 'Graphic theme'),
            'language' => Yii::t('cms', 'Main language'),
            'ajaxPager' => Yii::t('cms', 'Load pages in block without reloading whole web-page'),
            'ajaxPagerScroll' => Yii::t('cms', 'Scroll to block when navigate by pages'),
            'defaultsShowEmail' => Yii::t('cms', 'Who can see email address in user profile, by default'),
            'defaultsSendMessage' => Yii::t('cms', 'Who can send an email to user through the site, by default'),
            'userExtraFields' => Yii::t('cms', 'Extra user profile fields'),
            'cacheTime' => Yii::t('cms', 'Cache time'),
            'slugTransliterate' => Yii::t('cms', 'Transliterate page slug'),
            'slugLowercase' => Yii::t('cms', 'Lowercase page slug'),
            'timezone' => Yii::t('cms', 'Default timezone'),
		);
        $units = ContentUnit::getInstalledUnits();
        foreach ($units as $unitClass) {
            if (method_exists($unitClass, 'settings')) {
                $elems = call_user_func(array($unitClass, 'settings'), $unitClass);
                if (is_array($elems) && !empty($elems)) {
                    foreach ($elems as $k => $elem) {
                        if ($elem['label'])
                            $ret[$unitClass.'.'.$k] = $elem['label'];
                    }
                }
            }
        }
        return self::localizedLabels($ret);
	}

    public static function defaults()
    {
        return array(
            'adminEmail'=>Yii::app()->params['admin']['email'],
            'defaultsPerPage'=>10,
            'language'=>Yii::app()->language,
            'defaultsShowEmail'=>'registered',
            'defaultsSendMessage'=>'registered',
            'cacheTime'=>3600,
            'timezone'=>Yii::app()->params['timezone'],
        );
    }

    public function i18n()
    {
        return array('sitename');
    }

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'name' => 'string',
            'value' => 'text',
        );
    }

    public function form()
    {
        $timezoneList =timezone_identifiers_list();
        sort($timezoneList);
        $timezoneList = array_combine($timezoneList, $timezoneList);
        
        // Общие настроки
        $ret = array(
            'elements'=>array(
                Form::tab(Yii::t('cms', 'General settings')),
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
                'language'=>array(
                    'type'=>'LanguageSelect',
                    'empty'=>null,
                ),
                'timezone'=>array(
                    'type'=>'dropdownlist',
                    'items'=>$timezoneList,
                ),
                'autoSave'=>array(
                    'type'=>'checkbox'
                ),
                'slugTransliterate'=>array(
                    'type'=>'checkbox'
                ),
                'slugLowercase'=>array(
                    'type'=>'checkbox'
                ),
                Form::tab(Yii::t('cms', 'Appearance')),
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
                'ajaxPager'=>array(
                    'type'=>'checkbox'
                ),
                'ajaxPagerScroll'=>array(
                    'type'=>'checkbox'
                ),
                Form::tab(Yii::t('cms', 'Users')),
                'defaultsShowEmail'=>array(
                    'type'=>'dropdownlist',
                    'items'=>User::roles(),
                ),
                'defaultsSendMessage'=>array(
                    'type'=>'dropdownlist',
                    'items'=>User::roles(),
                ),
                'userExtraFields'=>array(
                    'type'=>'FieldSet',
                    
                ),
                Form::tab(Yii::t('cms', 'Performance')),
                'cacheTime'=>array(
                    'type'=>'Slider',
					'options'=>array(
						'min'=>0,
						'max'=>3600,
                        'step'=>60,
					),
                    'hint'=>Yii::t('cms', 'in seconds, 0 = off, 3600 - one hour'),
                ),
            ),
        );
        // Настройки для юнитов
        $units = ContentUnit::getInstalledUnits();
        $ret['elements'][] = Form::tab(Yii::t('cms', 'Units settings'));
        foreach ($units as $unitClass) {
            if (method_exists($unitClass, 'settings')) {
                $elems = call_user_func(array($unitClass, 'settings'), $unitClass);
                if (is_array($elems) && !empty($elems)) {
                    $ret['elements'][] = Form::section(call_user_func(array($unitClass, 'modelName')));
                    foreach ($elems as $k => $elem)
                    {
                        $ret['elements'][$unitClass.'.'.$k] = $elem;
                    }
                }
            }
        }
        return self::localizedForm($ret);
    }

    public function localizedForm($form)
    {
        $f = array();
        foreach ($form['elements'] as $k => $v) {
            $f[$k] = $v;
            if (is_array($v) && in_array($k, SiteSettingsForm::i18n())) {
                foreach (array_keys(I18nActiveRecord::getLangs(Yii::app()->language)) as $language)
                    $f[$language.'_'.$k] = $v;
            }
        }
        $form['elements'] = $f;
        return $form;
    }

    public function localizedRules($rules)
    {
        $ret = array();
        $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
        foreach ($rules as $rule) {
            $fields = explode(',',$rule[0]);
            $f = array();
            foreach ($fields as $field) {
                $field = trim($field);
                if (in_array($field, SiteSettingsForm::i18n())) {
                    foreach ($langs as $lang)
                        $f[] = $lang.'_'.$field;
                }
            }
            $rule[0] = implode(',',array_merge($fields, $f));
            $ret[] = $rule;
        }
        return $ret;
    }

    public function localizedLabels($labels)
    {
        $l = array();
        $langs = I18nActiveRecord::getLangs(Yii::app()->language);
        foreach ($labels as $k => $v) {
            $l[$k] = $v;
            if (in_array($k, SiteSettingsForm::i18n())) {
                foreach (array_keys($langs) as $lang)
                    $l[$lang.'_'.$k] = $v . ' [' . Yii::t('languages', $langs[$lang]) . ']';;
            }
        }
        return $l;
    }

    public function __get($name)
	{
        if (in_array($name, $this->i18n())) {
            $attr = Yii::app()->language . '_' . $name;
            return $this->$attr;
        } else if(isset($this->_attributes[$name]))
            return $this->_attributes[$name];
	}

	public function __set($name,$value)
	{
        if (in_array($name, $this->i18n())) {
            $attr = Yii::app()->language . '_' . $name;
            $this->$attr = $value;
        } else $this->setAttribute($name,$value);
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
	
	public function getAttributes($names=null)
	{
        $ret = parent::getAttributes($names);
        foreach ($this->i18n() as $field) {
            $ret[$field] = $this->$field;
        }
        return $ret;
		//return $this->_attributes;
	}

    public function  attributeNames()
    {
        return array_keys($this->_attributes);
    }
	
}