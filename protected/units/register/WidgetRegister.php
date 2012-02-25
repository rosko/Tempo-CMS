<?php


class WidgetRegister extends ContentWidget
{
    public function name($language=null)
    {
        return Yii::t('UnitRegister.main', 'Registration and profile form', array(), null, $language);
    }

    public function icon()
    {
        return '/images/icons/fatcow/16x16/user.png';
    }

    public function modelClassName()
    {
        return 'ModelRegister';
    }

    public function unitClassName()
    {
        return 'UnitRegister';
    }

    public function urlParam($method)
    {
        return $method;
    }

    public static function urlParams()
    {
        return array(
            'view', 'do'
        );
    }

    public function cacheable()
    {
        return false;
    }

    public function init()
    {
        parent::init();
        if (isset($_GET[$this->urlParam('do')])) {
            $this->params['doParam'] = $_GET[$this->urlParam('do')];
        }

        if (($this->params['isGuest'] || $this->params['editMode']) && $this->params['doParam']!='edit') {

            $model=new User('register');
            $makeForm = $model->makeForm('register', $this->params['content']->fields, $this->params['content']->fields_req);
            $this->params['formElements'] = $makeForm['elements'];
            $this->params['formRules'] = $makeForm['rules'];
            
            if(isset($_REQUEST['ajax-validate']))
            {
                echo CActiveForm::validate($model);
                Yii::app()->end();
            }

            if ($this->proccessRequest()) {
                if ($this->params['content']->is_emailauth_req) {
                    $this->params['waitingAuthCode'] = true;
                } else {
                    $this->params['justRegistered'] = true;
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

                    $cfg = ContentUnit::loadConfig();
                    $viewFileDir = $cfg['UnitRegister'].'.register.templates.mail.';
                    $tpldata = array(
                        'model'=>$user,
                        'settings' => Yii::app()->settings->model->getAttributes(),
                        'page' => $this->params['content']->getUnitPageArray(),
                    );
                    if ($this->params['content']->notify_user) {
                        // send 'to_user_notify' mail
                        Yii::app()->messenger->send(
                            'email',
                            $user->email,
                            Yii::t('UnitRegister.main', 'Registration completed'),
                            Yii::app()->controller->renderPartial(
                                $viewFileDir.'to_user_notify',
                                $tpldata,
                                true
                            )
                        );
                    }
                    $this->params['confirmedAuthCode'] = true;
                    unset($_REQUEST['authcode']);
                } else {
                    $this->params['faultAuthCode'] = true;
                }

            }

        } else {

            if ($this->params['isGuest']) {
                $this->params['accessDenied'] = true;
            } else {
                $makeForm = $this->params['user']->makeForm('update', $this->params['content']->profile_fields, $this->params['content']->profile_fields_req);
                $this->params['formElements'] = $makeForm['elements'];
                $this->params['formRules'] = $makeForm['rules'];

                $profileUnit = ModelProfiles::model()->find('unit_id > 0');
                $profileUnitWidget = new WidgetProfiles;
                if ($profileUnit)
                    $this->params['profileUnitUrl'] = $profileUnit->getUnitUrl();
                    $this->params['profileUnitUrlParams'] = $profileUnitWidget->urlParam('view').'='.$this->params['user']->id;

                if(isset($_REQUEST['ajax-validate']))
                {
                    echo CActiveForm::validate($this->params['user']);
                    Yii::app()->end();
                }
                if(isset($_POST['User']))
                {
                    $this->params['user']->attributes=$_POST['User'];
                    if ($this->params['user']->save()) {
                        Yii::app()->user->setFlash('save-permanent', Yii::t('UnitRegister.main','Profile edited successfully'));
                        Yii::app()->controller->refresh();
                    }
                }
            }
        }        
    }
    
    
    protected function proccessRequest($model=null)
    {
        if(isset($_POST['User']))
		{
            if (!$model) {
                $model = new User('register');
                $model->makeForm('register', $this->params['content']->fields, $this->params['content']->fields_req);
            }
            $tpldata = array();
			$model->attributes=$_POST['User'];
            if ($model->password == '') {
                $model->password = $model->password_repeat = $tpldata['generatedPassword'] = User::generatePassword();
                $model->askfill = true;
            }
			if($model->validate()) {
                $model->save(false);

                $cfg = ContentUnit::loadConfig();
                $viewFileDir = $cfg['UnitRegister'].'.register.templates.mail.';
                $tpldata['model'] = $model->getAttributes();
                $tpldata['settings'] = Yii::app()->settings->model->getAttributes();
                $tpldata['page'] = $this->params['content']->getUnitPageArray();

                if ($this->params['content']->notify_admin) {
                    // send 'to_admin_notify' mail
                    Yii::app()->messenger->send(
                        'email',
                        Yii::app()->settings->getValue('adminEmail'),
                        '['.$_SERVER['HTTP_HOST'].'] '. Yii::t('UnitRegister.main', 'New user registration'),
                        Yii::app()->controller->renderPartial(
                            $viewFileDir.'to_admin_notify',
                            $tpldata,
                            true
                        )
                    );
                }
                if ($this->params['content']->is_emailauth_req) {
                    $model->saveAttributes(array(
                        'authcode'=>User::hash($model->id.$model->login.time().rand())
                    ));
                    $tpldata['model'] = $model;
                    // send 'to_user_confirm' mail
                    Yii::app()->messenger->send(
                        'email',
                        $model->email,
                        Yii::t('UnitRegister.main', 'Registration confirm'),
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
                    if ($this->params['content']->notify_user || $tpldata['generatedPassword']) {
                        // send 'to_user_notify' mail
                        Yii::app()->messenger->send(
                            'email',
                            $model->email,
                            Yii::t('UnitRegister.main', 'Registration completed'),
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