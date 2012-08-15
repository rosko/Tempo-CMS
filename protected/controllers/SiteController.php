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
	    	else {
                $id = 1;
                if ($error['code'] == 403 && Yii::app()->settings->getValue('pageOnError403')) {
                    $id = Yii::app()->settings->getValue('pageOnError403');
                } elseif ($error['code'] == 404 && Yii::app()->settings->getValue('pageOnError404')) {
                    $id = Yii::app()->settings->getValue('pageOnError404');
                }
                $page = Page::model()->findbyPk(intval($id));
                if (!$page) $page = Page::model()->findbyPk(1);
                Yii::app()->page->model = $page;
	        	$this->render('error', $error);
            }
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
        if (Yii::app()->request->getQuery('model') !== null) {
            $modelClass = 'Model'.ucfirst(Yii::app()->request->getQuery('model'));
            ContentUnit::loadUnits();
            if (!FeedHelper::isFeedPresent($modelClass, Yii::app()->request->getQuery('id')===null))
                throw new CHttpException(404,Yii::t('cms', 'The requested page does not exist.'));

            // Фид определенного раздела
            if ((Yii::app()->request->getQuery('id') !== null)
                && ($content = call_user_func(array($modelClass, 'model'))->findByPk(intval(Yii::app()->request->getQuery('id')))))
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

    public function actionRebuild()
    {
        Yii::app()->installer->installAll();
    }

}
