<?php

class Role extends I18nActiveRecord
{
    const GUEST = 'guest';
    const ANYBODY = 'anybody';
    const AUTHENTICATED = 'authenticated';
    const ADMINISTRATOR = 'administrator';
    const AUTHOR = 'author';
    const EDITOR = 'editor';

    public function icon()
    {
        return '/images/icons/fatcow/16x16/user.png';
    }

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function modelName($language=null)
    {
        return Yii::t('cms', 'User roles', array(), null, $language);
    }

    public function tableName()
    {
        return Yii::app()->db->tablePrefix . 'roles';
    }

    public function rules()
    {
        return $this->localizedRules(array(
            array('name', 'match', 'pattern' => '/^[a-z]+[a-z0-9-]*[a-z0-9]+$/', 'message'=>Yii::t('cms', '{attribute} can only contain letters and numbers. And it can not start with a digit or sign')),
            array('title', 'required'),
            array('name', 'length', 'max' => 32),
            array('title', 'length', 'max' => 255, 'encoding' => 'UTF-8'),
            array('users', 'safe'),
        ));
    }

    public function attributeLabels()
    {
        return array(
            'id' => Yii::t('cms', 'ID'),
            'name' => Yii::t('cms', 'Role name'),
            'title' => Yii::t('cms', 'Title'),
            'users' => Yii::t('cms', 'Users'),
        );
    }

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'name' => 'char(32)',
            'title' => 'string',

        );
    }

    public function form()
    {
        return array(
            'elements'=>array(
                'name'=>array(
                    'type'=>'text',
                ),
                'title'=>array(
                    'type'=>'text',
                ),
                'users'=>array(
                    'type'=>'Select2',
                    'related'=>'users',
                    'showAttribute'=>'email,login,displayname',
                ),
            ),
        );

    }

    public function i18n()
    {
        return array(
            'title',
        );
    }

    public function relations()
    {
        return array(
            'users' => array(self::MANY_MANY, 'User', UserRole::tableName() . '(role_id,user_id)'),
        );
    }

    public function behaviors()
    {
        return array(
            'RelationsBehavior' => array(
                'class' => 'application.behaviors.RelationsBehavior',
            ),
        );
    }

    public function searchAttributes()
    {
        return array(
            'name', 'title',
        );
    }

    public function install()
    {
        $defaultRoles = array(
            self::ADMINISTRATOR => 'Administrator',
            self::EDITOR => 'Editor',
            self::AUTHOR => 'Author',
        );

        foreach ($defaultRoles as $roleId => $roleName) {

            $role = new self;
            $role->name = $roleId;;
            $role->title = Yii::t('cms', $roleName);
            $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
            foreach ($langs as $lang) {
                $role->{$lang . '_title'} = Yii::t('cms', $roleName, array(), null, $lang);
            }
            $role->save(false);

        }

    }

    public static function builtInRoles()
    {
        return array(
            self::GUEST => Yii::t('cms', 'Guest'),
            self::ANYBODY => Yii::t('cms', 'Anybody'),
            self::AUTHENTICATED => Yii::t('cms', 'Authenticated user'),
        );
    }

    public static function all()
    {
        $ret = self::builtInRoles();
        $roles = self::model()->findAll();
        foreach ($roles as $role) {
            $ret[$role->name] = $role->title;
        }
        return $ret;
    }

}