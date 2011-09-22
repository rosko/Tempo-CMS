<?php

class UnitProfiles extends Content
{
	const ICON = '/images/icons/fatcow/16x16/user.png';
    const HIDDEN = true;

    public function unitName($language=null)
    {
        return Yii::t('UnitProfiles.unit', 'User profiles', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_profiles';
	}

	public function rules()
	{
		return $this->localizedRules(array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id, per_page', 'numerical', 'integerOnly'=>true),
            array('table_fields, displayed_fields', 'type', 'type'=>'array'),
            array('feedback_form', 'safe'),
		));
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
            'table_fields' => Yii::t('UnitProfiles.unit', 'Fields in users table'),
            'displayed_fields' => Yii::t('UnitProfiles.unit', 'Displayed fields in user profile'),
            'per_page' => Yii::t('UnitProfiles.unit', 'Rows in table per page'),
            'feedback_form' => Yii::t('UnitProfiles.unit', 'Feedback form'),
		);
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
            'create' => 'datetime',
            'modify' => 'datetime',
            'table_fields' => 'text', // поля отображаемые в таблице пользователей
            'displayed_fields' => 'text', // поля отображаемые при просмотре профиля пользователя
            'per_page' => 'integer unsigned', // количество профилей в таблице на одну страницу
            'feedback_form' => 'text',
        );
    }

    public function behaviors()
    {
        return array(
            'CSerializeBehavior' => array(
                'class' => 'application.behaviors.CSerializeBehavior',
                'serialAttributes' => array('table_fields', 'displayed_fields', 'feedback_form'),
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
            'view', 'do', 'page', 'sort'
        );
        $ret = array();
        foreach ($list as $param) {
            $ret[] = self::urlParam($param);
        }
        return $ret;
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

    public function  cacheDependencies() {
        return array(
            array(
                'class'=>'system.caching.dependencies.CDbCacheDependency',
                'sql'=>'SELECT CONCAT(MAX(`create`),MAX(`modify`)) FROM `' . User::tableName() . '`',
            ),
        );
    }

    public static function dynamicEditProfileLink($param)
    {
        if (Yii::app()->user->id == $param['id']) {
            $registerUnit = UnitRegister::model()->find('unit_id > 0');
            if ($registerUnit) {
                return '<p>' . CHtml::link(Yii::t('UnitProfiles.unit', 'Edit profile'), $registerUnit->getUnitUrl()) . '</p>';
            }
        }
        return '';
    }

    public static function dynamicFeedbackForm($params)
    {
        $user = User::model()->findByPk($params['id']);
        if ($user && Yii::app()->user->checkAccess($user->send_message) && $user->id != Yii::app()->user->id && $user->email) {

            $vm = new VirtualModel($params['feedback_form'], 'FieldSet');
            $config = $vm->formMap;
            $config['id'] = sprintf('%x',crc32(serialize(array_keys($params['feedback_form']))));
            $config['buttons'] = array(
                'send'=>array(
                    'type'=>'submit',
                    'label'=>Yii::t('UnitProfiles.unit', 'Send'),
                ),
            );
            $profileVar = self::urlParam('view');
            $config['activeForm'] = Form::ajaxify($config['id']);
            $config['activeForm']['clientOptions']['validationUrl'] = '/?r=view/unit&pageUnitId='.$params['pageUnitId'].'&'.$profileVar.'='.$user->id;
            $config['activeForm']['clientOptions']['afterValidate'] = "js:function(f,d,h){if (!h) {return true;}}";
            $form = new Form($config, $vm);

            $ret = '<h3>' . Yii::t('UnitProfiles.unit', 'Feedback form') . '</h3>' ;
            if (Yii::app()->user->hasFlash('UnitProfilesSend-permanent'))
                $ret .= Yii::t('UnitProfiles.unit' , 'Your message was successfully sent');
            else
                $ret .= '<div class="form">' . $form->render() . '</div>';
            return $ret;
        }
        return '';
    }

	public static function form()
	{
        $model = new User;
        $viewFields = $model->proposedFields('view', true);

		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitProfiles.unit', 'Settings')),
                'table_fields'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$viewFields,
                ),
                'displayed_fields'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$viewFields,
                ),
				'per_page'=>array(
					'type'=>'Slider',
                    'hint'=>Yii::t('UnitProfiles.unit', 'If zero choosed, accordingly site\'s general settings'),
					'options'=>array(
						'min' => 0,
						'max' => 50,
                        'step' => 5,
					)
				),
                Form::tab(Yii::t('UnitProfiles.unit', 'Feedback form')),
                'feedback_form'=>array(
                    'type'=>'FieldSet',
                ),
                
            )
        );

    }

    public function prepare($params)
    {
        $params = parent::prepare($params);        
        $id = __CLASS__.$this->id;
        $params['profileVar'] = $this->urlParam('view');
        $blank = true;

        if (!empty($_REQUEST[$params['profileVar']])) {

            $profile = User::model()->findByPk(intval($_REQUEST[$params['profileVar']]));
            if ($profile) {

                $params['details'] = Yii::app()->controller->widget('zii.widgets.CDetailView', array(
                    'data'=>$profile,
                    'attributes'=>$this->makeFields($this->displayed_fields, $profile),
                ), true);
                $params['profile'] = $profile->getAttributes();

                if (Yii::app()->user->checkAccess($profile->send_message) && $profile->id != $params['user']->id && $profile->email) {

                    $vm = new VirtualModel($this->feedback_form, 'FieldSet');                    
                    $config = $vm->formMap;
                    $config['id'] = sprintf('%x',crc32(serialize(array_keys($this->feedback_form))));
                    $config['buttons'] = array(
                        'send'=>array(
                            'type'=>'submit',
                            'label'=>Yii::t('UnitProfiles.unit', 'Send'),
                        ),
                    );
                    $config['activeForm'] = Form::ajaxify($config['id']);
                    $config['activeForm']['clientOptions']['validationUrl'] = '/?r=view/unit&pageUnitId='.$params['pageUnit']->id.'&'.$params['profileVar'].'='.$profile->id;
                    $config['activeForm']['clientOptions']['afterValidate'] = "js:function(f,d,h){if (!h) {return true;}}";
                    $form = new Form($config, $vm);

                    if(isset($_REQUEST['ajax-validate']))
                    {
                        echo CActiveForm::validate($vm);
                        Yii::app()->end();
                    }

                    if ($form->submitted('send')) {
                        $vm = $form->model;
                        if ($form->validate()) {

                            $cfg = Unit::loadConfig();
                            $viewFileDir = $cfg['UnitProfiles'].'.UnitProfiles.templates.mail.';
                            $labels = $vm->attributeLabels();
                            foreach ($vm->getAttributes() as $attr => $value) {
                                $tpldata['fields'][$labels[$attr]] = $value;
                            }
                            $tpldata['profile'] = $profile->getAttributes();
                            $tpldata['settings'] = Yii::app()->settings->model->getAttributes();
                            $tpldata['page'] = $this->getUnitPageArray();
                            $registerUnit = UnitRegister::model()->find('unit_id > 0');
                            if ($registerUnit) {
                                $tpldata['profileEditUrl'] = $registerUnit->getUnitUrl();
                                $tpldata['profileEditUrlParams'] = $registerUnit->urlParam('do').'=edit';
                            }

                            Yii::app()->messenger->send(
                                'email',
                                $profile->email,
                                '['.$_SERVER['HTTP_HOST'].'] '. Yii::t('UnitProfiles.unit', 'Feedback form'),
                                Yii::app()->controller->renderPartial(
                                    $viewFileDir.'feedback',
                                    $tpldata,
                                    true
                                )
                            );
  
                            Yii::app()->user->setFlash('UnitProfilesSend-permanent', Yii::t('UnitProfiles.unit','Your message was successfully sent'));
                            Yii::app()->controller->refresh();
                        }
                    }

                    $params['feedbackForm'] = $form->render();

                }


            } else {
                $params['error'] = Yii::t('UnitProfiles.unit', 'Profile not found');
            }
            $blank = false;
        }

        if ($blank) {
            $params = $this->prepareTable($params);

        }
        return $params;
    }

    protected function prepareTable($params)
    {
        $id = __CLASS__.$this->id;
        $tableFields = $this->makeFields($this->table_fields);
        $urlparams = (bool)Yii::app()->settings->getValue('ajaxPager') ? array(
            'route'=>'view/unit',
            'params'=>array(
                'pageUnitId'=>$params['pageUnit']['id'],
            ),
        ) : array();

        $dataProvider=new CActiveDataProvider('User', array(
            'pagination'=>CMap::mergeArray(array(
                'pageVar'=>$this->urlParam('page'),
                'pageSize' => $this->per_page ? $this->per_page : Yii::app()->settings->getValue('defaultsPerPage'),
            ), $urlparams),
            'sort'=>CMap::mergeArray(array(
                'sortVar'=>$this->urlParam('sort'),
            ), $urlparams),
        ));
        $tableFields[] = array(
            'class'=>'CButtonColumn',
            'template'=>'{view}',
            'buttons'=>array(
                'view'=>array(
                    'label'=>Yii::t('UnitProfiles.unit', 'View profile'),
                    'url' => '"?'.$params['profileVar'].'={$data->id}"',
                ),
            ),
        );

        $params['table'] = Yii::app()->controller->widget('zii.widgets.grid.CGridView', array(
            'id'=>$id,
            'ajaxUpdate'=>(bool)Yii::app()->settings->getValue('ajaxPager') ? $id : false,
            'ajaxVar'=>$id,
            'dataProvider'=>$dataProvider,
            'columns'=>$tableFields,
            'selectableRows'=>0,
        ), true);

        return $params;
    }

    protected function makeFields($fields, $user=null)
    {
        if (!is_array($fields)) $fields = array();
        $u = new User;
        $specialFields = $u->specialFields('view');

        $fields = array_diff($fields, $specialFields['unsafe']);
        $form = User::form();
        foreach ($form['elements'] as $name => $field) {
            if ($field['type']=='Fields') {
                foreach ($field['config'] as $extraField) {
                    if (in_array($extraField['name'], $fields)) {
                        if ($user) {
                            foreach ($fields as $i => $attr) {
                                if ($attr == $extraField['name']) {
                                    $fields[$i] = array(
                                        'label'=>$extraField['label'][Yii::app()->language],
                                        'value'=>$user->{$name}[$extraField['name']],
                                        'name'=>$extraField['name'],
                                    );
                                }
                            }
                        } else {
                            foreach ($fields as $i => $attr) {
                                if ($attr == $extraField['name']) {
                                    $fields[$i] = array(
                                        'header'=>$extraField['label'][Yii::app()->language],
                                        'value'=>'$data->'.$name.'['.$extraField['name'].']',
                                        'name'=>$extraField['name'],
                                    );
                                }
                            }
                        }
                    }
                }
                if (in_array($name, $fields))
                    $fields = array_diff($fields, array($name));
            }
        }
        foreach ($fields as $key => $field) {
            if ($field=='email') {
                if ($user) {
                    $fields[$key] = array(
                        'name'=>'email',
                        'value'=>$user->email,
                        'visible'=>Yii::app()->user->checkAccess($user->show_email) || $user->id == Yii::app()->user->id,
                    );
                } else {
                    $fields[$key] = array(
                        'name'=>'email',
                        'value'=>'Yii::app()->user->checkAccess($data->show_email) ? $data->email : ""',
                    );
                }
            }
        }
        return $fields;


    }

}