<?php

class SiteController extends Controller
{
    public $defaultAction = 'login';

    public function actions()
    {
        return array(
            'captcha'=>Yii::app()->params['captcha'],
        );
    }
    
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

    public function actionFeed($type='rss')
    {
        header('Content-Type: application/rss+xml');
        if (isset($_GET['model'])) {
            $modelClass = 'Model'.$_GET['model'];
            ContentUnit::loadUnits();
            if (!FeedHelper::isFeedPresent($modelClass, !isset($_GET['id'])))
                throw new CHttpException(404,Yii::t('cms', 'The requested page does not exist.'));

            // Фид определенного раздела
            if (isset($_GET['id']) && ($content = call_user_func(array($modelClass, 'model'))->findByPk(intval($_GET['id']))))
            {
                FeedHelper::renderFeed($type, $content);
            // Фид всех записей этого типа
            } 
            else
            {
                FeedHelper::renderFeed($type, $modelClass);
            }
        // Общий фид
        } else {
            FeedHelper::renderFeed($type);
        }
    }
	
    public function actionJsI18N($language)
    {
        header('Content-type: text/javascript');
        Yii::app()->language = $language;
        $this->renderPartial('/jsI18N');
    }

}
