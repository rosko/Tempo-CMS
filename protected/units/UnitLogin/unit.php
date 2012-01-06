<?php

class UnitLogin extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/user.png';
    }
    
    public function hidden()
    {
        return true;
    }

    public function unitName($language=null)
    {
        return Yii::t('UnitLogin.main', 'Login Form', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_login';
	}

	public function rules()
	{
		return array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',

		);
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
            'create' => 'datetime',
            'modify' => 'datetime',
        );
    }

    public function templateVars()
    {
        return array(
            '{$formButtons}' => Yii::t('UnitLogin.main', 'LoginForm buttons'),
        );
    }

    public function cacheVaryBy()
    {
        return array(
            'isGuest'=>Yii::app()->user->isGuest,
        );
    }

    public function cacheRequestTypes()
    {
        return array('GET');
    }

    public function urlParams()
    {
        return array('authcode');
    }

}

class UnitLoginWidget extends ContentWidget
{
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
                    $cfg = Unit::loadConfig();
                    $viewFileDir = $cfg['UnitLogin'].'.UnitLogin.templates.mail.';
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
        if (Yii::app()->user->id)
            $user = User::model()->findByPk(Yii::app()->user->id);
        if ($user) {
            return '<h3>' . Yii::t('UnitLogin.main', 'Hello') . ', ' . $user->displayname . '!</h3>';
        }
        return '';
    }

    
}