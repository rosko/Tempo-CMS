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
				'actions'=>array('siteSettings', 'rights', 'rightsAcoUpdate', 'siteMap'),
                'roles'=>array(Role::ADMINISTRATOR),
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
     * Управляет правами доступа
     */
    public function actionRights()
    {
        $this->render('rights');
    }

    public function actionRightsAcoUpdate($aco_class, $aco_key, $aco_value, $operation, $value, $is_deny=0)
    {
        $ret = true;

        $savedItems = AccessItem::model()->findAllByAttributes(
            array(
                 'aco_class' => $aco_class,
                 'aco_key' => $aco_key,
                 'aco_value' => $aco_value,
                 'action' => $operation,
                 'is_deny' => $is_deny
            )
        );

        $items = explode(',', $value);
        foreach ($items as $item) {

            list($aro_class, $aro_key, $aro_value) = explode(':', $item);

            $alreadySaved = false;
            foreach ($savedItems as $i => $saveItem) {

                if ($saveItem['aro_class'] == $aro_class &&
                    $saveItem['aro_key'] == $aro_key &&
                    $saveItem['aro_value'] == $aro_value) {

                    $alreadySaved = true;
                    unset($savedItems[$i]);
                    break;

                }

            }

            if (!$alreadySaved) {

                $accessItem = new AccessItem();
                $accessItem->aco_class = $aco_class;
                $accessItem->aco_key = $aco_key;
                $accessItem->aco_value = $aco_value;
                $accessItem->aro_class = $aro_class;
                $accessItem->aro_key = $aro_key;
                $accessItem->aro_value = $aro_value;
                $accessItem->action = $operation;
                $accessItem->is_deny = (bool)$is_deny;
                $ret = $ret && $accessItem->save();

            }

        }

        foreach ($savedItems as $saveItem) {
            $saveItem->delete();
        }

        echo intval($ret);
    }

    /**
     * Отображает карту сайта
     */
	public function actionSiteMap()
	{
		$tree = Page::model()->allowed('update')->getTree();
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