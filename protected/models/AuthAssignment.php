<?php
class AuthAssignment extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->authManager->assignmentTable;
	}

    public function scheme()
    {
        return array(
            'itemname'=>'char(64)',
            'userid'=>'integer unsigned',
            'bizrule'=>'text',
            'data'=>'text',
        );
    }

    public function install()
    {
        $sql = 'alter table `' . self::tableName() . '` add primary key (`itemname`, `userid`)';
        Yii::app()->db->createCommand($sql)->execute();
        $sql = 'alter table `' . self::tableName() . '` add foreign key (`itemname`) references `'.AuthItem::tableName().'` (`name`) on delete cascade on update cascade';
        Yii::app()->db->createCommand($sql)->execute();

        $auth=Yii::app()->authManager;
        $bizRule='return Yii::app()->user->isGuest;';
        $auth->createRole('guest', 'Guest', $bizRule);

        $bizRule='return !Yii::app()->user->isGuest;';
        $role = $auth->createRole('authenticated', 'Authenticated user', $bizRule);
        $role->addChild('guest');

        $role = $auth->createRole('superadmin', 'Super administrator');
        $role->addChild('authenticated');

        $auth->assign('superadmin', User::getAdmin()->id);

        $classes = array('Page', 'User', 'Settings', 'Unit');
        foreach ($classes as $className) {
            if (method_exists($className, 'defaultAccess')) {
                $a = call_user_func(array($className, 'defaultAccess'));
                foreach ($a as $operation => $role) {
                    $auth->createOperation($operation.$className, $className.' '.$operation);
                    if (is_array($role)) {
                        foreach ($role as $r) {
                            $auth->addItemChild($r, $operation.$className);
                        }
                    } else
                        $auth->addItemChild($role, $operation.$className);
                }
            }
        }

    }
}