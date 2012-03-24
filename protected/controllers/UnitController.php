<?php

class UnitController extends Controller
{
    public function filters()
    {
        return array('accessControl');
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('install'),
                'users'=>array('@'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    /**
     * Страница инсталляции/деинсталляции блоков
     */
    public function actionInstall()
    {
        $allUnits = ContentUnit::getAvailableUnits();
        $errors = array();
        if (isset($_POST['Units'])) {
            $units = array_keys($_POST['Units']);
            ContentUnit::install($units);
            $uninstall = array_diff(array_keys($allUnits), $units);
            foreach ($uninstall as $i=>$className) {
                $sql = 'SELECT count(*) FROM `' . Widget::tableName() . '` WHERE `class` = :class';
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(':class', $className, PDO::PARAM_STR);
                $exists = $command->queryScalar();
                if ($exists) {
                    unset($uninstall[$i]);
                    $errors[] = Yii::t('cms', 'Can\`t unistall "{name}"', array('{name}'=>$allUnits[$className]['name']));
                }
            }
            ContentUnit::uninstall($uninstall);
            $allUnits = ContentUnit::getAvailableUnits();
        }

        $this->render('install', array(
                'units' => $allUnits,
                'errors' => $errors,
            ));
    }

}