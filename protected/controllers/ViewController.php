<?php
/**
 * ViewController class file
 *
 * @author Alexey Volkov <a@insvit.com>
 * @link http://www.insvit.com/
 * @copyright Copyright &copy; 2010-2011 Alexey Volkov
 *
 */

/**
 * ViewController - это класс контроллера, который в общем отвечает
 * за отображение созданных страниц и отдельных блоков
 */
class ViewController extends Controller
{
    /**
     * Отображает созданную страницу
     */
	public function actionIndex()
	{
//        TODO: Доделать перемещение блоков, бывают некритические ошибки
//        $ret = PageWidget::checkIntegrity();
//        if ($ret['percents'] > 0) {
//            echo '<pre>';
//            print_r ($ret);
//            echo '</pre>';
//        }
        $page = Yii::app()->page->model;
        if ($page===null) {
            throw new CHttpException(404,Yii::t('cms', 'The requested page does not exist.'));
        }
        if ($page->redirect) {
            if (Yii::app()->user->isGuest)
                $this->redirect($page->redirect);
            else
                Yii::app()->user->setFlash('redirect-permanent-hint', Yii::t('cms', 'This page has redirection to') . '<a href="'.$page->redirect . '">'.$page->redirect.'</a>. <a class="ui-button-icon" href="" onclick="$(\'#toolbar_edit\').click();return false;">'.Yii::t('cms', 'Page properties').'</a>');
        }

		$this->render('index',array(
			'model'=>$page,
		));
        Yii::app()->user->getFlashes(true);
	}

    /**
     * Отображает блок
     *
     * @param int $pageWidgetId id страничного блока
     */
    public function actionWidget($pageWidgetId)
    {
		$pageWidget = PageWidget::model()->with('widget')->findByPk((int)$pageWidgetId);
        if ($pageWidget) {
            $pageWidget->widget->content->widget($pageWidget->widget->class, array(
                'pageWidget'=>$pageWidget,
            ));
        }
    }

}
