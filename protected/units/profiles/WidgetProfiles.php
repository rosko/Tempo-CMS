<?php

class WidgetProfiles extends ContentWidget
{
    public function name($language=null)
    {
        return Yii::t('UnitProfiles.main', 'User profiles', array(), null, $language);
    }

    public function icon()
    {
        return '/images/icons/fatcow/16x16/user.png';
    }

    public function modelClassName()
    {
        return 'ModelProfiles';
    }

    public function unitClassName()
    {
        return 'UnitProfiles';
    }

    public function urlParam($method)
    {
        return $method;
    }

    public static function urlParams()
    {
        return array(
            'view', 'do', 'page', 'sort'
        );
    }

    public function cacheRequestTypes()
    {
        return array('GET');
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
        $id = __CLASS__.$this->params['content']->id;
        $this->params['profileVar'] = $this->urlParam('view');
        $blank = true;

        if (!empty($_REQUEST[$this->params['profileVar']])) {

            $profile = User::model()->findByPk(intval($_REQUEST[$this->params['profileVar']]));
            if ($profile) {

                $this->params['details'] = Yii::app()->controller->widget('zii.widgets.CDetailView', array(
                    'data'=>$profile,
                    'attributes'=>$this->makeFields($this->params['content']->displayed_fields, $profile),
                ), true);
                $this->params['profile'] = $profile->getAttributes();

                if (Yii::app()->user->checkAccess($profile->send_message) && $profile->id != $this->params['user']->id && $profile->email) {

                    $vm = new VirtualModel($this->params['content']->feedback_form, 'FieldSet');                    
                    $config = $vm->formMap;
                    $config['id'] = sprintf('%x',crc32(serialize(array_keys($this->params['content']->feedback_form))));
                    $config['buttons'] = array(
                        'send'=>array(
                            'type'=>'submit',
                            'label'=>Yii::t('UnitProfiles.main', 'Send'),
                        ),
                    );
                    $config['activeForm'] = Form::ajaxify($config['id']);
                    $config['activeForm']['clientOptions']['validationUrl'] = '/?r=view/widget&pageWidgetId='.$this->params['pageWidget']->id.'&'.$this->params['profileVar'].'='.$profile->id;
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

                            $cfg = ContentUnit::loadConfig();
                            $viewFileDir = $cfg['UnitProfiles'].'.profiles.templates.mail.';
                            $labels = $vm->attributeLabels();
                            foreach ($vm->getAttributes() as $attr => $value) {
                                $tpldata['fields'][$labels[$attr]] = $value;
                            }
                            $tpldata['profile'] = $profile->getAttributes();
                            $tpldata['settings'] = Yii::app()->settings->model->getAttributes();
                            $tpldata['page'] = $this->params['content']->getWidgetPageArray();
                            $registerModel = ModelRegister::model()->find('widget_id > 0');
                            $registerWidget = new WidgetRegister;
                            if ($registerUnit) {
                                $tpldata['profileEditUrl'] = $registerModel->getWidgetUrl();
                                $tpldata['profileEditUrlParams'] = $registerWidget->urlParam('do').'=edit';
                            }

                            Yii::app()->messenger->send(
                                'email',
                                $profile->email,
                                '['.$_SERVER['HTTP_HOST'].'] '. Yii::t('UnitProfiles.main', 'Feedback form'),
                                Yii::app()->controller->renderPartial(
                                    $viewFileDir.'feedback',
                                    $tpldata,
                                    true
                                )
                            );
  
                            Yii::app()->user->setFlash('UnitProfilesSend-permanent', Yii::t('UnitProfiles.main','Your message was successfully sent'));
                            Yii::app()->controller->refresh();
                        }
                    }

                    $this->params['feedbackForm'] = $form->render();

                }


            } else {
                $this->params['error'] = Yii::t('UnitProfiles.main', 'Profile not found');
            }
            $blank = false;
        }

        if ($blank) {
            $this->prepareTable();

        }       
    }

    protected function prepareTable()
    {
        $id = __CLASS__.$this->params['content']->id;
        $tableFields = $this->makeFields($this->params['content']->table_fields);
        $urlparams = (bool)Yii::app()->settings->getValue('ajaxPager') ? array(
            'route'=>'view/widget',
            'params'=>array(
                'pageWidgetId'=>$this->params['pageWidget']['id'],
            ),
        ) : array();

        $dataProvider=new CActiveDataProvider('User', array(
            'pagination'=>CMap::mergeArray(array(
                'pageVar'=>$this->urlParam('page'),
                'pageSize' => $this->params['content']->per_page ? $this->params['content']->per_page : Yii::app()->settings->getValue('defaultsPerPage'),
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
                    'label'=>Yii::t('UnitProfiles.main', 'View profile'),
                    'url' => '"?'.$this->params['profileVar'].'={$data->id}"',
                ),
            ),
        );

        $this->params['table'] = Yii::app()->controller->widget('zii.widgets.grid.CGridView', array(
            'id'=>$id,
            'ajaxUpdate'=>(bool)Yii::app()->settings->getValue('ajaxPager') ? $id : false,
            'ajaxVar'=>$id,
            'dataProvider'=>$dataProvider,
            'columns'=>$tableFields,
            'selectableRows'=>0,
        ), true);

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
    
    public static function dynamicEditProfileLink($params)
    {
        if (Yii::app()->user->id == $params['id']) {
            $registerModel = ModelRegister::model()->find('widget_id > 0');
            if ($registerModel) {
                return '<p>' . CHtml::link(Yii::t('UnitProfiles.main', 'Edit profile'), $registerModel->getWidgetUrl()) . '</p>';
            }
        }
        return '';
    }

    public static function dynamicFeedbackForm($params)
    {
        $user = User::model()->findByPk($params['id']);
        if ($user && Yii::app()->user->checkAccess($user->send_message) && $user->id != Yii::app()->user->id && $user->email) 
        {
            $vm = new VirtualModel($params['feedback_form'], 'FieldSet');
            $config = $vm->formMap;
            $config['id'] = sprintf('%x',crc32(serialize(array_keys($params['feedback_form']))));
            $config['buttons'] = array(
                'send'=>array(
                    'type'=>'submit',
                    'label'=>Yii::t('UnitProfiles.main', 'Send'),
                ),
            );
            $profileVar = 'view';
            $config['activeForm'] = Form::ajaxify($config['id']);
            $config['activeForm']['clientOptions']['validationUrl'] = '/?r=view/widget&pageWidgetId='.$params['pageWidgetId'].'&'.$profileVar.'='.$user->id;
            $config['activeForm']['clientOptions']['afterValidate'] = "js:function(f,d,h){if (!h) {return true;}}";
            $form = new Form($config, $vm);

            $ret = '<h3>' . Yii::t('UnitProfiles.main', 'Feedback form') . '</h3>' ;
            if (Yii::app()->user->hasFlash('WidgetProfilesSend-permanent'))
                $ret .= Yii::t('UnitProfiles.main' , 'Your message was successfully sent');
            else
                $ret .= '<div class="form">' . $form->render() . '</div>';
            return $ret;
        }
        return '';
    }

    
}