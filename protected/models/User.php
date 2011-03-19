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
            array('login, password', 'required', 'on'=>'register'),
			array('login, email, password', 'length', 'max'=>32, 'min'=>5, 'encoding'=>'UTF-8'),
            //array('login', 'match', 'not'=>true, 'pattern'=>'/^[0-9]*/u'),
            array('login, email', 'unique'),
			array('email, name', 'required'),
            array('login', 'match', 'pattern'=>'/^[a-z]+[a-z0-9-]*[a-z0-9]+$/', 'message'=>Yii::t('cms', '{attribute} can only contain letters and numbers. And it can not start with a digit or sign')),
            array('password', 'match', 'pattern'=>'/^[[:graph:]]*$/', 'message'=>Yii::t('cms', '{attribute} can only contain letters and numbers')),
            array('email', 'email'),
            array('password', 'compare', 'compareAttribute'=>'password_repeat'),
            array('password, password_repeat, authcode', 'safe'),
            array('login', 'unsafe', 'on'=>'edit'),
			array('name', 'length', 'max'=>64, 'encoding'=>'UTF-8'),
            array('active, askfill, show_email, send_message', 'unsafe', 'on'=>'register'),
            array('active, askfill, agreed', 'unsafe', 'on'=>'update'),
            array('active, askfill, agreed', 'boolean'),
            array('extra_fields', 'safe'),
            array('captcha', 'captcha', 'on'=>'register',
                'allowEmpty'=>!CCaptcha::checkRequirements() || !Yii::app()->user->isGuest,
                'captchaAction'=>'site/captcha'),
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
                ),
                'password_repeat'=>array(
                    'type'=>'password',
                ),
                'active'=>array(
                    'type'=>'checkbox',
                ),
                'captcha'=>array(
                    'type'=>'text',
                    'label'=>Yii::app()->controller->widget("CCaptcha", array(
                        'captchaAction'=>'site/captcha',
                        'clickableImage'=>true,

                    ), true) . '<br />'. Yii::t('cms', 'Verify code'),
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
                    'config'=>Yii::app()->settings->getValue('userExtraFields'),
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

    public function getExtraFields($what=null)
    {
        $ef = Yii::app()->settings->getValue('userExtraFields');
        $lang = Yii::app()->language;
        if (!$what) {
            $ret = $ef;
        } elseif ($what == 'labels') {
            $ret = array();
            foreach ($ef as $k => $v) {
                $ret[$v['name']] = $v['label'][$lang];
            }
        }
        return $ret;
    }
}