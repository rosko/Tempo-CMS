<?php

class User extends CActiveRecord
{
	const ICON = '/images/icons/fatcow/16x16/user.png';

    const ADMIN_LOGIN = 'admin';
    private static $_admin;
    public $rules;
    public $captcha;

    public $password_repeat='';

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function name($language=null)
    {
        return $this->login ? $this->login : Yii::t('cms', 'New user', array(), null, $language);
    }

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'users';
	}

	public function rules()
	{
        // add, edit - действия администратора
        // register, update - действия пользователя
        if ($this->rules) return $this->rules;
		return array(
            array('login, password, password_repeat', 'filter', 'filter'=>'strtolower'),
            array('login, password, password_repeat', 'filter', 'filter'=>'trim'),
            array('login, password', 'required', 'on'=>'add'),
//            array('login, password', 'required', 'on'=>'register'),
			array('login, email, password', 'length', 'max'=>32, 'min'=>5, 'encoding'=>'UTF-8'),
            //array('login', 'match', 'not'=>true, 'pattern'=>'/^[0-9]*/u'),
            array('login, email', 'unique'),
			array('email', 'required'),
            array('name', 'required', 'on'=>'update'),
            array('login', 'match', 'pattern'=>'/^[a-z]+[a-z0-9-]*[a-z0-9]+$/', 'message'=>Yii::t('cms', '{attribute} can only contain letters and numbers. And it can not start with a digit or sign')),
            array('password', 'match', 'pattern'=>'/^[[:graph:]]*$/', 'message'=>Yii::t('cms', '{attribute} can only contain letters and numbers')),
            array('email', 'email'),
            array('password', 'compare', 'compareAttribute'=>'password_repeat'),
            array('password, password_repeat, authcode', 'safe'),
            array('login', 'unsafe', 'on'=>array('edit','update')),
			array('name', 'length', 'max'=>64, 'encoding'=>'UTF-8'),
            array('active, askfill, show_email, send_message', 'unsafe', 'on'=>array('register', 'view')),
            array('active, askfill, captcha, agreed', 'unsafe', 'on'=>array('update','view')),
            array('active, askfill, agreed', 'boolean'),
            array('show_email, send_message', 'safe'),
            array('extra_fields', 'FieldsValidator', 'config'=>User::extraFields()),
            array('captcha', 'captcha', 'on'=>'register',
                'allowEmpty'=>!CCaptcha::checkRequirements() || !Yii::app()->user->isGuest,
                'captchaAction'=>'site/captcha'),
            array('password, password_repeat, captcha', 'unsafe', 'on'=>'view'),
		);
	}

    public function behaviors()
    {
        return array(
            'CSerializeBehavior' => array(
                'class' => 'application.behaviors.CSerializeBehavior',
                'serialAttributes' => array('extra_fields'),
            )
        );
    }

	public function attributeLabels()
	{
		return array(
			'id' => Yii::t('cms', 'ID'),
			'login' => Yii::t('cms', 'Username'),
			'password' => Yii::t('cms', 'Password'),
            'password_repeat' => Yii::t('cms', 'Repeat password'),
			'email' => Yii::t('cms', 'E-mail'),
			'name' => Yii::t('cms', 'Name'),
            'active' => Yii::t('cms', 'Active'),
            'captcha'=> Yii::t('cms', 'Verify code'),
            'agreed'=> Yii::t('cms', 'I agree with the agreement'),
            'askfill'=>Yii::t('cms', 'Ask the user to fill out profile'),
            'show_email'=>Yii::t('cms', 'Who can see your email address'),
            'send_message'=>Yii::t('cms', 'Who can send you an email through the site'),
            'extra_fields'=>Yii::t('cms', 'Extra fields'),
		);
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'login' => 'char(32)',
            'password' => 'char(64)',
            'email' => 'char(64)',
            'name' => 'char(64)',
            'active'=>'boolean',
            'authcode' => 'char(64)',
            'agreed'=>'boolean',
            'askfill'=>'boolean',
            'show_email'=>'char(32)',
            'send_message'=>'char(32)',
            'extra_fields'=>'text',
        );
    }

    public function userCategories()
    {
        return array(
            'all'=>Yii::t('cms', 'All visitors'),
            'registered'=>Yii::t('cms', 'Registered users'),
            'none'=>Yii::t('cms', 'Nobody'),
        );
    }

    public function isUserInCategory($category)
    {
        $cond = array(
            'all'=>true,
            'registered'=>!Yii::app()->user->isGuest,
            'none'=>false,
        );
        return $cond[$category];
    }

    public function form()
    {
        return array(
            'elements'=>array(
                'login'=>array(
                    'type'=>'text',
                ),
                'name'=>array(
                    'type'=>'text',
                ),
                'email'=>array(
                    'type'=>'text',
                ),
                'password'=>array(
                    'type'=>'password',
                    'value'=>'',
                    'hint'=>!Yii::app()->user->isGuest ? Yii::t('cms', 'Enter your password only if you want to replace it') : '',
                ),
                'password_repeat'=>array(
                    'type'=>'password',
                ),
                'active'=>array(
                    'type'=>'checkbox',
                ),
                'captcha'=>array(
                    'type'=>'text',
                    'label'=>Yii::t('cms', 'Verify code') . Yii::app()->controller->widget("CCaptcha", array(
                        'captchaAction'=>'site/captcha',
                        'clickableImage'=>true,

                    ), true),
                    'hint'=>Yii::t('cms', 'Enter the symbols from the image'),
                ),
                'agreed'=>array(
                    'type'=>'radio',
                ),
                'askfill'=>array(
                    'type'=>'checkbox',
                ),
                'show_email'=>array(
                    'type'=>'dropdownlist',
                    'items'=>User::userCategories(),
                ),
                'send_message'=>array(
                    'type'=>'dropdownlist',
                    'items'=>User::userCategories(),
                ),
                'extra_fields'=>array(
                    'type'=>'Fields',
                    'config'=>User::extraFields(),
                )
            ),
        );
    }

    public function defaultAccess()
    {
        return array(
            'create'=>'superadmin',
            'read'=>'authenticated',
            'update'=>'superadmin',
            'delete'=>'superadmin',
        );
    }

    public function install()
    {
        $user = new self;
        $user->login = self::ADMIN_LOGIN;
        $user->name = Yii::app()->params['admin']['name'];
        $user->email = Yii::app()->params['admin']['email'];
        $user->password = self::hash(Yii::app()->params['admin']['password']);
        $user->active = true;
        $user->save(false);
    }

    public static function hash($string)
	{
        return sha1(md5($string) . Yii::app()->params['hashSalt']);
	}

    public static function generatePassword($length=8)
    {
        $chars = array_merge(range(0,9), range('a','z'), range('A','Z'));
        shuffle($chars);
        return implode(array_slice($chars, 0, $length));
    }

    public function beforeSave()
    {
        if (!$this->password) {
            unset($this->password);
        } else {
            $this->password = self::hash($this->password);
        }
        if (!$this->show_email) {
            $this->show_email = Yii::app()->settings->getValue('defaultsShowEmail');
        }
        if (!$this->send_message) {
            $this->send_message = Yii::app()->settings->getValue('defaultsSendMessage');
        }
        return parent::beforeSave();
    }

    public function beforeDelete()
    {
        if ($this->id == 1 || $this->login == self::ADMIN_LOGIN) {
            return false;
        }
        return parent::beforeDelete();
    }

    public static function getByLogin($login)
    {
        return self::$_admin = self::model()->find('`login` = :login', array(':login'=>$login));
    }

    public static function getAdmin()
    {
		if(self::$_admin===null)
            self::$_admin = self::getByLogin(self::ADMIN_LOGIN);
        return self::$_admin;
    }

    public function proposedFields($scenario='', $proposeRequired=false)
    {
        $specialFields = $this->specialFields($scenario);
        $form = User::form();
        $_fields = array_diff(array_keys($form['elements']), $specialFields['unsafe']);
        if (!$proposeRequired) {
            $_fields = array_diff($_fields, $specialFields['required']);
        }
        $labels = User::attributeLabels();

        $fields = array();
        foreach ($_fields as $field) {
            $fields[$field] = $labels[$field];
        }
        foreach (User::extraLabels() as $field => $label) {
            $fields[$field] = Yii::t('cms', 'Extra field').': '.$label;
        }
        return $fields;
    }

    public function makeForm($scenario='', $selectedFields=array(), $requiredFields=array())
    {
        $this->rules = null;
        $specialFields = $this->specialFields($scenario);
        $form = User::form();
        
        if (!is_array($selectedFields))
            $selectedFields = array();        
        if (!is_array($requiredFields))
            $requiredFields = array();

        $fields = array_unique(array_diff(array_merge(array_merge($specialFields['required'], $requiredFields), $selectedFields), $specialFields['unsafe']));
        foreach ($fields as $name) {
            if (isset($form['elements'][$name])) {
                $formFields[$name] = $form['elements'][$name];
            }
        }
        foreach ($formFields as $name => $field) {
            if ($field['type']=='Fields') {
                if (is_array($field['config'])) foreach ($field['config'] as $key => $extraField) {
                    if (!in_array($extraField['name'], $fields)) {
                        unset($formFields[$name]['config'][$key]);
                    }
                }
            }
        }
        $oldRules = $this->rules();

        $alreadyRequired = array();
        $rules = array();
        foreach ($oldRules as $rule) {
            if ($scenario) {
                $scenarios = isset($rule['on']) ? (is_array($rule['on']) ? $rule['on'] : explode(',',str_replace(' ','',$rule['on']))) : null;
            }
            if (!$scenario || !$scenarios || in_array($scenario,$scenarios)) {
                if(isset($rule[0],$rule[1])) {
                    if ($rule[1]=='captcha' && !in_array($rule[0],$selectedFields)) continue;
                    if ($rule[1]=='required'/* || $rule[1]=='compare'*/)  {
                        $validator = CValidator::createValidator($rule[1],$this,$rule[0],array_slice($rule,2));
                        foreach ($validator->attributes as $attr) {
                            if (!in_array($attr, $requiredFields) && !in_array($attr, $specialFields['required'])) {
                                $validator->attributes = array_diff($validator->attributes, array($attr));
                            } else {
                                $alreadyRequired[] = $attr;
                            }
                        }
                        $rule[0] = implode(', ', $validator->attributes);
                    }
                    if ($rule[1]=='FieldsValidator') {
                        $alreadyRequired[] = $rule[0];
                        if (is_array($rule['config'])) foreach ($rule['config'] as $key => $field) {
                            if (!in_array($field['name'], $selectedFields)) {
                                unset($rule['config'][$key]);
                                continue;
                            }

                            $isRequired = in_array($field['name'], $requiredFields);

                            if (!is_array($rule['config'][$key]['rules']))
                                $rule['config'][$key]['rules'] = array();
                            foreach ($rule['config'][$key]['rules'] as $i => $_rule) {
                                if ($_rule[0]=='required') {
                                    if (!in_array($field['name'], $requiredFields))
                                        unset($rule['config'][$key]['rules'][$i]);
                                    $isRequired = false;
                                    $alreadyRequired[] = $field['name'];
                                }
                            }
                            if ($isRequired) {
                                $rule['config'][$key]['rules'][] = array('required');
                                $alreadyRequired[] = $field['name'];
                            }
                        }
                    }
                    if ($rule[0])
                        $rules[] = $rule;
                }
            }
        }
        $neededRequired = array_diff($requiredFields, $alreadyRequired);
        if (!empty($neededRequired)) {
            $rule = array(implode(', ',$neededRequired), 'required');
            if ($scenario)
                $rule['on'] = $scenario;
            $rules[] = $rule;
        }

        if ($rules) foreach ($rules as $rule) {
            if ($scenario) {
                $scenarios = isset($rule['on']) ? (is_array($rule['on']) ? $rule['on'] : explode(',',str_replace(' ','',$rule['on']))) : null;
            }
            if (!$scenario || !$scenarios || in_array($scenario,$scenarios)) {
                if ($rule[1]=='FieldsValidator') {
                    $attributes = explode(',',str_replace(' ','',$rule[0]));
                    foreach ($attributes as $attr) {
                        if (isset($formFields[$attr]))
                            $formFields[$attr]['config'] = $rule['config'];
                    }
                }
            }
        }

        $this->rules = $rules;
        return array(
            'elements'=>$formFields,
            'rules'=>$rules,
        );
        
    }

    public static function extraLabels()
    {
        $language = Yii::app()->language;
        $labels = array();
        foreach (User::extraFields() as $field) {
            $labels[$field['name']] = $field['label'][$language];
        }
        return $labels;

    }

    public static function extraFields()
    {
        return Yii::app()->settings->getValue('userExtraFields');
    }

    public function specialFields($scenario='')
    {
        $requiredFields = array();
        $unsafeFields = array();
        foreach ($this->rules() as $rule) {
            if ($scenario) {
                $scenarios = isset($rule['on']) ? (is_array($rule['on']) ? $rule['on'] : explode(',',str_replace(' ','',$rule['on']))) : null;
            }
            if (!$scenario || !$scenarios || in_array($scenario,$scenarios)) {
                if ($rule[1]=='unsafe') {
                    $unsafeFields = array_merge($unsafeFields, explode(',',str_replace(' ','',$rule[0])));
                }
                if ($rule[1]=='required') {
                    $requiredFields = array_merge($requiredFields, explode(',',str_replace(' ','',$rule[0])));
                }
            }
        }
        return array(
            'required'=>$requiredFields,
            'unsafe'=>$unsafeFields,
        );
    }
}