<?php

class WidgetLogin extends ContentWidget
{
    public function name($language=null)
    {
        return Yii::t('UnitLogin.main', 'Login Form', array(), null, $language);
    }

    public function icon()
    {
        return '/images/icons/fatcow/16x16/user.png';
    }
    
    public function modelClassName()
    {
        return 'ModelLogin';
    }

    public function unitClassName()
    {
        return 'UnitLogin';
    }

    public static function urlParams()
    {
        return array('authcode');
    }

    public function cacheVaryBy()
    {
        return array(
            'isGuest'=>Yii::app()->user->isGuest,
        );
    }

    public function init()
    {
        parent::init();
        $this->params['formButtons'] = array(
            'login'=>array(
                'type'=>'submit',
                'label'=>Yii::t('UnitLogin.main', 'Login'),
                'title'=>Yii::t('UnitLogin.main', 'Login'),
            ),
        );
        $this->params['doRemember'] = isset($_POST['RememberForm']);

        if ($this->proccessRequest()) {
            if($this->params['doRemember']) {
                $this->params['doneRemember'] = true;

            } else
                Yii::app()->controller->refresh();
        }
        if (!empty($_REQUEST['authcode'])) {
            $user = User::model()->find('`authcode`=:authcode', array('authcode'=>$_REQUEST['authcode']));
            if ($user) {
                $identity = new AuthCodeIdentity($_REQUEST['authcode']);
                $identity->authenticate();
                if($identity->errorCode===UserIdentity::ERROR_NONE) {
                    Yii::app()->user->login($identity);
                }
                $user->saveAttributes(array(
                    'authcode'=>'',
                    'askfill'=>true,
                ));
                Yii::app()->controller->refresh();
            }
        }
        
    }

    public function cacheRequestTypes()
    {
        return array('GET');
    }

    
    protected function proccessRequest()
    {
        if(isset($_POST['logout'])) {
            Yii::app()->user->logout();
            Yii::app()->controller->refresh();
            return true;
        }
		if(isset($_POST['LoginForm']))
		{
            $model=new LoginForm;
			$model->attributes=$_POST['LoginForm'];
			if($model->validate() && $model->login()) {
                Yii::app()->controller->refresh();
                return true;
			}
		}
        if(isset($_POST['RememberForm']))
        {
            $model=new RememberForm;
            $model->attributes=$_POST['RememberForm'];
            if($model->validate()) {

                $user = User::model()->find('`login` = :username OR `email` = :username', array('username'=>$model->username));
                if ($user) {
                    $user->saveAttributes(array(
                        'authcode'=>User::hash($user->id.$user->email.time().rand())
                    ));
                    $cfg = ContentUnit::loadConfig();
                    $viewFileDir = $cfg['UnitLogin'].'.login.templates.mail.';
                    $tpldata['model'] = $user;
                    $tpldata['settings'] = Yii::app()->settings->model->getAttributes();
                    $tpldata['page'] = $this->params['content']->getUnitPageArray();
                    // send 'to_user_confirm' mail
                    Yii::app()->messenger->send(
                        'email',
                        $user->email,
                        Yii::t('UnitLogin.main', 'Password reset'),
                        Yii::app()->controller->renderPartial(
                            $viewFileDir.'password_reset',
                            $tpldata,
                            true
                        )
                    );
                    return true;
                }
            }
        }
        return false;

    }
   
    public function dynamicGreetings()
    {
        $user = Yii::app()->user->data;
        if ($user) {
            return '<h3>' . Yii::t('UnitLogin.main', 'Hello') . ', ' . $user->displayname . '!</h3>';
        }
        return '';
    }

    
}