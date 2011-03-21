<?php

class UnitLogin extends Content
{
	const ICON = '/images/icons/fatcow/16x16/user.png';
    const HIDDEN = true;

    public function name($language=null)
    {
        return Yii::t('UnitLogin.unit', 'Login Form', array(), null, $language);
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
			array('unit_id', 'required'),
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
        );
    }

    public function templateVars()
    {
        return array(
            '{$formButtons}' => Yii::t('UnitLogin.unit', 'LoginForm buttons'),
        );
    }

    public function prepare($params)
    {
        $params = parent::prepare($params);
        $params['formButtons'] = array(
            'login'=>array(
                'type'=>'submit',
                'label'=>Yii::t('UnitLogin.unit', 'Login'),
                'title'=>Yii::t('UnitLogin.unit', 'Login'),
            ),
        );
        $params['doRemember'] = isset($_POST['RememberForm']);

        if ($this->proccessRequest()) {
            if($params['doRemember']) {
                $params['doneRemember'] = true;
                
            } else
                Yii::app()->controller->refresh();
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
                    'authcode'=>'',
                    'askfill'=>true,
                ));
                Yii::app()->controller->refresh();
            }
        }
        return $params;
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
                    $tpldata['page'] = $this->getUnitPageArray();
                    // send 'to_user_confirm' mail
                    Yii::app()->messenger->send(
                        'email',
                        $user->email,
                        Yii::t('UnitLogin.unit', 'Password reset'),
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

}