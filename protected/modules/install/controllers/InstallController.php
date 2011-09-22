<?php

Yii::import('application.modules.install.components.*');

class InstallController extends Controller
{
	public function filters()
	{
		return array(
//			'accessControl',
		);
	}

	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array(
                    'index', 'make', 'ajax',
                ),
				'users'=>array('admin'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

    public function  renderPartial($view, $data = null, $return = false, $processOutput = false)
    {
        $data['assets'] = Yii::app()->getAssetManager()->publish($this->getModule()->getBasePath().'/assets');
        return parent::renderPartial($view, $data, $return, $processOutput);
    }

    public function actionIndex()
    {
        $config = $this->getModule()->config();
        $step = (!empty($_POST['step']) && $_POST['step'] > 0) ? intval($_POST['step']) : 0;
        if (!empty($_POST['previous'])) $step--;
        if (!empty($_POST['next'])) $step++;
        do {
            $result = array();
            $status = true;
            $backward = false;
            if (!empty($config['wizard'][$step]))
            foreach ($config['wizard'][$step] as $command) {
                if (empty($command[1])) $command[1] = 'run';
                $className = 'InstallCommand'.ucfirst($command[0]);
                if (!empty($command[0]) && method_exists($className, $command[1])) {
                    $command = call_user_func(array($className, $command[1]), $command);
                    if (!empty($comand['backward'])) $backward = true;
                    $result[] = $command;
                    $status = $status && (!empty($command['status']) || !empty($command['canSkip']));
                }
            }
            if ($backward) {
                $step--;
            } else $step++;
        }
        while ($config['mode']=='silent' && ($status || $backward));
        $this->render('index', array(
            'result'=>$result,
            'step'=>$step-1,
            'config'=>$config,
        ));
    }

    public function actionMake()
    {
        $config = $this->getModule()->config();
        $step = (!empty($_GET['step']) && $_GET['step'] > 0) ? intval($_GET['step']) : 0;
        if (!empty($_POST['previous'])) $step--;
        if (!empty($_POST['next'])) $step++;
        
        if (!empty($config['steps'][$step])) {
            $stepName = $config['steps'][$step][0];
            $className = 'InstallModel'.ucfirst($stepName);
            class_exists($className);
            $model = new $className();
            $form_array = $model->form();
            $form_array['id'] = sprintf('%x',crc32(serialize(array_keys($model->attributes))));
            echo $stepName;
            $form_array['action'] = '/?r=install/install/make&step='.$step;
            $form_array['elements']['step'] = array(
                'type'=>'hidden',
                'value'=>$step,
            );
            if (!empty($config['steps'][$step-1])) {
                $form_array['buttons']['previous'] = array(
                    'type'=>'submit',
                    'label'=>Yii::t('InstallModule.main', 'Previous'),
                );
            }
            $form_array['buttons']['refresh'] =array(
                'type'=>'submit',
                'label'=>Yii::t('InstallModule.main', 'Refresh'),
            );
            if (!empty($config['steps'][$step+1])) {
                $form_array['buttons']['next'] = array(
                    'type'=>'submit',
                    'label'=>Yii::t('InstallModule.main', 'Next'),
                );
            }
            $form_array['activeForm'] = array(
                'class' => 'CActiveForm',
                'enableAjaxValidation' => true,
                'id' => $form_array['id'],
                'focus' => 'input[type="text"]',
                'clientOptions'=>array(
                    'ajaxVar'=>'ajax-validate',
                    'validateOnSubmit'=>true,
                    'validateOnChange'=>true,
                    'validateOnType'=>false,
//                    'afterValidate'=> 'js:function(f,d,h){ajaxSubmitForm(f,d,h);}'
                ),                
            );
            $form = new CForm($form_array, $model);
            $this->performAjaxValidation($model);
            echo $form;
        }        
    }

    public function actionAjax($cmd)
    {
        $className = 'InstallAction'.ucfirst($cmd);
        if (class_exists($className)) {
            $this->runAction(new $className($this, $cmd));
        }
    }

	protected function performAjaxValidation($model, $attributes=null, $loadInput=true)
	{
		if(isset($_REQUEST['ajax-validate']))
		{
            echo CActiveForm::validate($model, $attributes, $loadInput);
			Yii::app()->end();
		}
	}
}
