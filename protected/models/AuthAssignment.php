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
        $auth->createRole('anybody', 'Anybody');

        $bizRule='return Yii::app()->user->isGuest;';
        $role = $auth->createRole('guest', 'Guest', $bizRule);
        $role->addChild('anybody');

        $bizRule='return !Yii::app()->user->isGuest;';
        $role = $auth->createRole('authenticated', 'Authenticated user', $bizRule);
        $role->addChild('anybody');

        $role = $auth->createRole('author', 'Author');
        $role->addChild('authenticated');

        $role = $auth->createRole('editor', 'Editor');
        $role->addChild('authenticated');

        $role = $auth->createRole('administrator', 'Administrator');
        $role->addChild('authenticated');

        $auth->assign('administrator', User::getAdmin()->id);

        $classes = array('Page', 'Settings', 'Unit', 'User');
        foreach ($classes as $className) {
            if (method_exists($className, 'operations')) {
                $a = call_user_func(array($className, 'operations'));
                foreach ($a as $operation => $params) {
                    $auth->createOperation($operation, $params['label'], isset($params['bizRule']) ? $params['bizRule'] : null);
                    if (is_array($params['defaultRoles']))
                        foreach ($params['defaultRoles'] as $role) {
                            $auth->addItemChild($role, $operation);
                        }
                }
            }
            if (method_exists($className, 'tasks')) {
                $a = call_user_func(array($className, 'tasks'));
                foreach ($a as $task => $params) {
                    $auth->createTask($task, $params['label'], isset($params['bizRule']) ? $params['bizRule'] : null);
                    if (is_array($params['children']))
                        foreach ($params['children'] as $operation) {
                            $auth->addItemChild($task, $operation);
                        }
                    if (is_array($params['defaultRoles']))
                        foreach ($params['defaultRoles'] as $role) {
                            $auth->addItemChild($role, $task);
                        }
                }
            }
        }

    }
}