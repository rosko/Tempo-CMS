<?php

class UnitList extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/newspaper_link.png';
    }
    
    public function hidden()
    {
        return true;
    }
    
	public function unitName($language=null)
    {
        return Yii::t('UnitList.main', 'List', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_list';
	}

	public function rules()
	{
		return array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('class_name', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
			array('rule', 'length', 'max'=>255, 'encoding'=>'UTF-8'),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
            'class_name' => Yii::t('UnitList.main', 'List type'),
			'rule' => Yii::t('UnitList.main', 'Which entries to show'),
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
                'class_name'=>array(
                    'type'=>'ComboBox',
                    'array'=>Unit::getTypeNames(),
                ),
				'rule'=> array(
                    'type' => 'Scopes',
                    'classNameAttribute' => 'class_name',
                )
			),
		);
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
            'create' => 'datetime',
            'modify' => 'datetime',
            'class_name' => 'char(64)',
            'rule' => 'string',
        );
    }
    public function templateVars()
    {
        return array(
            '{$items}' => Yii::t('UnitList.main', 'Entries'),
        );
    }

    public function cacheDependencies() {
        $sql = '';
        $ret = array();
        if (Yii::$classMap[$this->class_name]) {
            $rule = $this->makeRule();
            eval("\$sql = {$this->class_name}::model()->{$rule}getSql('MAX(`modify`)');");
        }
        if ($sql) {
            $ret = array(
                array(
                    'class'=>'system.caching.dependencies.CDbCacheDependency',
                    'sql'=>$sql['sql'],
                    'params'=>$sql['params'],
                ),
            );
        }
        return $ret;
    }

    public function makeRule()
    {
        $rule = $this->rule;
        if ($rule)
            $rule .= '->';
        if (method_exists($this->class_name, 'scopes')) {
            $scopes = call_user_func(array($this->class_name, 'scopes'));
            if (isset($scopes['public']))
                $rule = 'public()->'.$rule;
        }
        return $rule;
    }

}

class UnitListWidget extends ContentWidget
{
    public function init()
    {
        parent::init();
        $this->params['items'] = array();
        if (Yii::$classMap[$this->params['content']->class_name]) {
            $rule = $this->params['content']->makeRule();
            eval("\$items = {$this->params['content']->class_name}::model()->{$rule}findAll();");
            foreach ($items as $item)
            {
                $this->params['items'][] = $item->widget($this->params['content']->class_name, array(), true);
            }
        }        
    }
}