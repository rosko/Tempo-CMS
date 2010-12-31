<?php

class SiteController extends Controller
{
    public $defaultAction = 'login';
    
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}

	public function actionLogin()
	{
		if (Yii::app()->user->checkAccess('updatePage')) {
			if ($_SERVER['HTTP_REFERER'] && strpos($_SERVER['HTTP_REFERER'],'/site/login')===false &&
                 strpos($_SERVER['HTTP_REFERER'],'://'.$_SERVER['HTTP_HOST'])===false)
				$this->redirect($_SERVER['HTTP_REFERER']);
			else
				$this->redirect(Yii::app()->homeUrl);
		}
		
		$model=new LoginForm;

		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		if ($_POST['backurl']) {
			$url = $_POST['backurl'];
		} else {
			$url = $_SERVER['HTTP_REFERER'];
		}

		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			if($model->validate() && $model->login()) {
				if ($url)
					$this->redirect($url);
				else
					$this->redirect(Yii::app()->user->returnUrl);
			}
		}
		$this->render('login',array('model'=>$model, 'url'=>$url));
	}

	public function actionLogout()
	{
		Yii::app()->user->logout();
		if ($_SERVER['HTTP_REFERER'])
			$this->redirect($_SERVER['HTTP_REFERER']);
		else
			$this->redirect(Yii::app()->homeUrl);
	}
	
}