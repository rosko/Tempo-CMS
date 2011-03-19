<?php

class UnitRegister extends Content
{
	const ICON = '/images/icons/fatcow/16x16/user.png';
    const HIDDEN = true;

    public function name($language=null)
    {
        return Yii::t('UnitRegister.unit', 'Register Form', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_register';
	}

	public function rules()
	{
		return $this->localizedRules(array(
			array('unit_id', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
            array('is_emailauth_req, is_invite_req, notify_admin, notify_user', 'boolean'),
            array('fields, fields_req', 'type', 'type'=>'array'),
            array('invites, agreement, text', 'safe'),
		));
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
            'fields' => Yii::t('UnitRegister.unit', 'Form fields'),
            'fields_req' => Yii::t('UnitRegister.unit', 'Required fields'),
            'is_emailauth_req' => Yii::t('UnitRegister.unit', 'Is e-mail authorization needed?'),
            'is_invite_req' => Yii::t('UnitRegister.unit', 'Is invite required?'),
            'invites' => Yii::t('UnitRegister.unit', 'Invites'),
            'notify_admin' => Yii::t('UnitRegister.unit', 'Notify administrator about new user'),
            'notify_user' => Yii::t('UnitRegister.unit', 'Notify user after successfull registration'),
            'agreement' => Yii::t('UnitRegister.unit', 'User agreement'),
            'text' => Yii::t('UnitRegister.unit', 'Text'),
		);
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
            'fields' => 'text',
            'fields_req' => 'text',
            'is_emailauth_req'=>'boolean',
            'notify_admin'=>'boolean',
            'notify_user'=>'boolean',
            'is_invite_req'=>'boolean',
            'invites'=>'text',
            'agreement'=>'text',
            'text'=>'text',
        );
    }

    public function i18n()
    {
        return array('agreement', 'text');
    }

    public function behaviors()
    {
        return array(
            'CSerializeBehavior' => array(
                'class' => 'application.behaviors.CSerializeBehavior',
                'serialAttributes' => array('fields', 'fields_req'),
            )
        );
    }

    public static function defaultRegFields()
    {
        return array('email', 'active', 'askfill', 'show_email', 'send_message');
    }

	public static function form()
	{
        $arr = User::form();
        $labels = User::attributeLabels();
        $fields_array = array();
        $default_fields = UnitRegister::defaultRegFields();
        foreach (array_keys($arr['elements']) as $k) {
            if (in_array($k, $default_fields)) continue;
            $fields_array[$k] = $labels[$k];
        }
        $extra_fields = User::getExtraFields('labels');
        foreach ($extra_fields as $k => $v) {
            $fields_array[$k] = '-'.$v;
        }
		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitRegister.unit', 'Settings')),
                'fields'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$fields_array,
                ),
                'fields_req'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$fields_array,
                ),
                'is_emailauth_req'=>array(
                    'type'=>'checkbox',
                ),
                'notify_admin'=>array(
                    'type'=>'checkbox',
                ),
                'notify_user'=>array(
                    'type'=>'checkbox',
                ),
                Form::tab(Yii::t('UnitRegister.unit', 'Text')),
                'text'=>array(
                    'type'=>'VisualTextAreaFCK'
                ),
                Form::tab(Yii::t('UnitRegister.unit', 'User agreement')),
                'agreement'=>array(
                    'type'=>'VisualTextAreaFCK'
                ),
// TODO: Поддержку инвайтов сделаем позже
/*                'is_invite_req'=>array(
                    'type'=>'checkbox',
                ),
                'invites'=>array(
                    'type'=>'textarea',
                    'rows'=>'10',
                    'cols'=>'50',
                )*/
            )
        );
        
    }

    public function templateVars()
    {
        return array(
            // TODO
        );
    }

    public function prepare($params)
    {
        $params = parent::prepare($params);
        $f = User::form();
        $form_array = array();
        $arr = is_array($this->fields) ? $this->fields : array();
        $fields = array_merge(UnitRegister::defaultRegFields(), $arr);
        foreach ($f['elements'] as $k => $v) {
            if (in_array($k, $fields))
                $form_array['elements'][$k] = $v;
        }
        foreach ($form_array['elements']['extra_fields']['config'] as $k => $v) {
            if (!in_array($v['name'], $fields))
                unset($form_array['elements']['extra_fields']['config'][$k]);
        }
        $params['formElements'] = $form_array['elements'];
        $params['formRules'] = $this->makeValidationRules(new User);
        if ($this->proccessRequest()) {
            if ($this->is_emailauth_req) {
                $params['waitingAuthCode'] = true;
            } else {
                $params['justRegistered'] = true;
            }
        }
        if ($_REQUEST['authcode']) {
            $user = User::model()->find('`authcode`=:authcode', array('authcode'=>$_REQUEST['authcode']));
            if ($user) {
                $identity = new AuthCodeIdentity($_REQUEST['authcode']);
                $identity->authenticate();
                if($identity->errorCode===UserIdentity::ERROR_NONE) {
                    Yii::app()->user->login($identity);
                }
                $user->saveAttributes(array(
                    'active'=>true,
                    'authcode'=>'',
                ));

                $cfg = Unit::loadConfig();
                $viewFileDir = $cfg['UnitRegister'].'.UnitRegister.templates.mail.';
                $tpldata = array(
                    'model'=>$user,
                    'settings' => Yii::app()->settings->model->getAttributes(),
                    'page' => $this->getUnitPageArray(),
                );
                if ($this->notify_user) {
                    // send 'to_user_notify' mail
                    Yii::app()->messenger->send(
                        'email',
                        $user->email,
                        Yii::t('UnitRegister.unit', 'Registration completed'),
                        Yii::app()->controller->renderPartial(
                            $viewFileDir.'to_user_notify',
                            $tpldata,
                            true
                        )
                    );
                }                
                $params['confirmedAuthCode'] = true;
                unset($_REQUEST['authcode']);
            } else {
                $params['faultAuthCode'] = true;
            }

        }

        return $params;
    }

    public function ajax($vars)
    {
        $model=new User('register');
        $model->rules = $this->makeValidationRules($model);
		if(isset($_REQUEST['ajax-validate']))
		{
            echo CActiveForm::validate($model);
			Yii::app()->end();
		}
        if ($this->proccessRequest($model)) {
            echo '1';
        }
        parent::ajax();
    }

    protected function proccessRequest($model=null)
    {
        if(isset($_POST['User']))
		{
            if (!$model) {
                $model = new User('register');
                $model->rules = $this->makeValidationRules($model);
            }
            $tpldata = array();
			$model->attributes=$_POST['User'];
            if ($model->password == '') {
                $model->password = $tpldata['generatedPassword'] = User::generatePassword();
                $model->askfill = true;
            }
			if($model->validate()) {
                $model->save(false);

                $cfg = Unit::loadConfig();
                $viewFileDir = $cfg['UnitRegister'].'.UnitRegister.templates.mail.';
                $tpldata['model'] = $model->getAttributes();
                $tpldata['settings'] = Yii::app()->settings->model->getAttributes();
                $tpldata['page'] = $this->getUnitPageArray();

                if ($this->notify_admin) {
                    // send 'to_admin_notify' mail
                    Yii::app()->messenger->send(
                        'email',
                        Yii::app()->settings->getValue('adminEmail'),
                        '['.$_SERVER['HTTP_HOST'].'] '. Yii::t('UnitRegister.unit', 'New user registration'),
                        Yii::app()->controller->renderPartial(
                            $viewFileDir.'to_admin_notify',
                            $tpldata,
                            true
                        )
                    );
                }
                if ($this->is_emailauth_req) {
                    $model->saveAttributes(array(
                        'authcode'=>User::hash($model->id.$model->login.time().rand())
                    ));
                    $tpldata['model'] = $model;
                    // send 'to_user_confirm' mail
                    Yii::app()->messenger->send(
                        'email',
                        $model->email,
                        Yii::t('UnitRegister.unit', 'Registration confirm'),
                        Yii::app()->controller->renderPartial(
                            $viewFileDir.'to_user_confirm',
                            $tpldata,
                            true
                        )
                    );
                    return true;
                } else {
                    $model->saveAttributes(array(
                        'active'=>true
                    ));
                    if ($this->notify_user || $tpldata['generatedPassword']) {
                        // send 'to_user_notify' mail
                        Yii::app()->messenger->send(
                            'email',
                            $model->email,
                            Yii::t('UnitRegister.unit', 'Registration completed'),
                            Yii::app()->controller->renderPartial(
                                $viewFileDir.'to_user_notify',
                                $tpldata,
                                true
                            )
                        );
                    }
                    $loginForm = new LoginForm;
                    $loginForm->username = $model->email;
                    $loginForm->password = !empty($_POST['User']['password']) ? $_POST['User']['password'] : $tpldata['generatedPassword'];
                    if ($loginForm->login()) {
                        return true;
                    }
                }
            }
		}
        return false;
    }

    protected function makeValidationRules($model)
    {
        if (!is_array($this->fields_req)) $this->fields_req = array();
        if (!is_array($this->fields)) $this->fields = array();
        $model->rules = null;
        $oldrules = $model->rules();
        $present = array();
        $rules = array();
        foreach ($oldrules as $rule) {
			if(isset($rule[0],$rule[1])) {
                if ($rule[1]=='captcha' && !in_array($rule[0],$this->fields)) continue;
                if ($rule[1]=='required' || $rule[1]=='compare')  {
                    $validator = CValidator::createValidator($rule[1],$this,$rule[0],array_slice($rule,2));
                    foreach ($validator->attributes as $attr) {
                        if (!in_array($attr, $this->fields_req)) {
                            if (!in_array($attr, UnitRegister::defaultRegFields())) {
                                $validator->attributes = array_diff($validator->attributes, array($attr));
                            }
                        } else {
                            $present[] = $attr;
                        }
                    }
                    $rule[0] = implode(', ', $validator->attributes);
                }
                if ($rule[0])
                    $rules[] = $rule;
            }
        }
        $rules[] = array(implode(', ',array_diff($this->fields_req, $present)), 'required', 'on'=>'register');
        return $rules;
    }
}