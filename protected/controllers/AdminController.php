<?php
/**
 * AdminController
 *
 * @author Alexey Volkov <a@insvit.com>
 * @link http://www.insvit.com/
 * @copyright Copyright &copy; 2010-2011 Alexey Volkov
 *
 */

/**
 * AdminController - это класс административного контроллера
 */
class AdminController extends Controller
{
	public function filters()
	{
		return array('accessControl');
	}

	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array('siteSettings', 'siteMap'),
				'users'=>array('@'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}
    /**
     * Редактирует свойства сайта
     */
	public function actionSiteSettings()
	{
		//$settingsForm = ;
		$form_array = SiteSettingsForm::form();
		//$form_array['title'] = Yii::t('cms', 'General settings');
        $form_array['id'] = 'SiteSettings';
        $form_array['activeForm'] = Form::ajaxify($form_array['id']);
		$form_array['buttons'] = array(
				'refresh'=>array(
					'type'=>'submit',
					'label'=>Yii::t('cms', 'Save'),
					'title'=>Yii::t('cms', 'Save and reload the page'),
				),
			);
		$form = new Form($form_array);
        $form->id = $form_array['id'];
		$form->model = clone Yii::app()->settings->model;

        $form->loadData();
        $this->performAjaxValidation($form->model, null, false);

		if ($form->submitted('refresh')) {
            if ($form->model->validate()) {
                Yii::app()->settings->saveAll($form->model->getAttributes());
                Yii::app()->installer->installAll(false);
                echo '1';
            } else {
                echo '0';
            }
            Yii::app()->end();
		}

        $caption = array(
            'icon' => 'settings',
            'label' => Yii::t('cms', 'Site settings'),
        );
		$this->render('/form', array('form'=>$form, 'caption'=>$caption));

	}

    /**
     * Отображает карту сайта
     */
	public function actionSiteMap()
	{
		$tree = Page::model()->getTree();
		$initially_open = array();
		$opened_levels = Yii::app()->request->isAjaxRequest ? 1 : 2;
		foreach ($tree as $pages)
		{
			foreach ($pages as $p)
			{
				if (substr_count($p['path'],',') < $opened_levels)
					$initially_open[] = 'page-'.$p['id'];
			}
		}
		$this->render('sitemap', compact('tree', 'initially_open'));
	}


}