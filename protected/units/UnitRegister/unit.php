<?php

class UnitRegister extends Content
{
	const ICON = '/images/icons/fatcow/16x16/user.png';
    const HIDDEN = true;
    const CACHE = false;

    public function unitName($language=null)
    {
        return Yii::t('UnitRegister.unit', 'Registration and profile form', array(), null, $language);
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
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
            array('is_emailauth_req, is_invite_req, notify_admin, notify_user', 'boolean'),
            array('fields, fields_req, profile_fields, profile_fields_req', 'type', 'type'=>'array'),
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
            'profile_fields' => Yii::t('UnitRegister.unit', 'Editable fields in user profile'),
            'profile_fields_req' => Yii::t('UnitRegister.unit', 'Required editable fields in user profile'),
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
            'create' => 'datetime',
            'modify' => 'datetime',
            'fields' => 'text',
            'fields_req' => 'text',
            'profile_fields' => 'text', // поля, которые пользователь может заполнить в своем профиле
            'profile_fields_req' => 'text', // поля, которые пользователь обязан заполнить в своем профиле
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
                'serialAttributes' => array('fields', 'fields_req', 'profile_fields', 'profile_fields_req'),
            )
        );
    }

    public function urlParam($method)
    {
        return 'profile_'.$method;
    }

    public function urlParams()
    {
        $list = array(
            'view', 'do'
        );
        $ret = array();
        foreach ($list as $param) {
            $ret[] = self::urlParam($param);
        }
        return $ret;
    }

    public static function defaultRegFields()
    {
        return array('email');
    }

    public static function restrictedRegFields()
    {
        return array('active', 'askfill', 'show_email', 'send_message');
    }

	public static function form()
	{
        $model = new User;
        $registerFields = $model->proposedFields('register', true);
        $updateFields = $model->proposedFields('update', true);
        
		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitRegister.unit', 'Settings')),
                'fields'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$registerFields,
                ),
                'fields_req'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$registerFields,
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
					'type'=>'TextEditor',
                    'kind'=>'fck',
                ),
                Form::tab(Yii::t('UnitRegister.unit', 'User agreement')),
                'agreement'=>array(
					'type'=>'TextEditor',
                    'kind'=>'fck',
                ),
                Form::tab(Yii::t('UnitRegister.unit', 'Editing profile')),
                'profile_fields'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$updateFields,
                ),
                'profile_fields_req'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$updateFields,
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
        if (isset($_GET[$this->urlParam('do')])) {
            $params['doParam'] = $_GET[$this->urlParam('do')];
        }

        if (($params['isGuest'] || $params['editMode']) && $params['doParam']!='edit') {

            $model=new User('register');
            $makeForm = $model->makeForm('register', $this->fields, $this->fields_req);
            $params['formElements'] = $makeForm['elements'];
            $params['formRules'] = $makeForm['rules'];
            
            if(isset($_REQUEST['ajax-validate']))
            {
                echo CActiveForm::validate($model);
                Yii::app()->end();
            }

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

        } else {

            if ($params['isGuest']) {
                $params['accessDenied'] = true;
            } else {
                $makeForm = $params['user']->makeForm('update', $this->profile_fields, $this->profile_fields_req);
                $params['formElements'] = $makeForm['elements'];
                $params['formRules'] = $makeForm['rules'];

                $profileUnit = UnitProfiles::model()->find('unit_id > 0');
                if ($profileUnit)
                    $params['profileUnitUrl'] = $profileUnit->getUnitUrl();
                    $params['profileUnitUrlParams'] = $profileUnit->urlParam('view').'='.$params['user']->id;

                if(isset($_REQUEST['ajax-validate']))
                {
                    echo CActiveForm::validate($params['user']);
                    Yii::app()->end();
                }
                if(isset($_POST['User']))
                {
                    $params['user']->attributes=$_POST['User'];
                    if ($params['user']->save()) {
                        Yii::app()->user->setFlash('save-permanent', Yii::t('UnitRegister.unit','Profile edited successfully'));
                        Yii::app()->controller->refresh();
                    }
                }
            }
        }

        return $params;
    }

    protected function proccessRequest($model=null)
    {
        if(isset($_POST['User']))
		{
            if (!$model) {
                $model = new User('register');
                $model->makeForm('register', $this->fields, $this->fields_req);
            }
            $tpldata = array();
			$model->attributes=$_POST['User'];
            if ($model->password == '') {
                $model->password = $model->password_repeat = $tpldata['generatedPassword'] = User::generatePassword();
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

}