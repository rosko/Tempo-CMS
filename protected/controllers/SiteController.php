<?php

class SiteController extends Controller
{
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
		if (!Yii::app()->user->isGuest) {
			if ($_SERVER['HTTP_REFERER'])
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
		if ($_POST['url']) {
			$url = $_POST['url'];
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
	
	public function actionSettings()
	{
		$this->layout = 'blank';
		$this->render('settings');
	}

}