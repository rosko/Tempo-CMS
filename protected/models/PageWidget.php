<?php

class PageWidget extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'pages_widgets';
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'page_id' => 'integer unsigned',
            'widget_id' => 'integer unsigned',
            'area' => 'char(32)',
            'order' => 'integer unsigned',
        );
    }

	public function rules()
	{
		return array(
			array('page_id, widget_id, area, order', 'required'),
			array('page_id, widget_id, order', 'numerical', 'integerOnly'=>true),
			array('area', 'length', 'max'=>32),
		);
	}

	public function relations()
	{
		return array(
			'widget' => array(self::BELONGS_TO, 'Widget', 'widget_id')
		);
	}
	
	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
			'page_id' => Yii::t('cms', 'Page'),
//			'widget_id' => Yii::t('cms', 'Widget'),
			'area' => Yii::t('cms', 'Page area'),
//			'order' => Yii::t('cms', 'Order'),
		);
	}
	
	/**
     * Возвращает идентификатор блока которому принадлежит pageWidget
     * @param integer идентификатор pageWidget`а
     * @return integer  идентификатор блока (Widget)
     */
    public static function getWidgetIdById($id)
	{
		$sql = 'SELECT widget_id FROM `' . self::tableName() . '` WHERE id = :id';
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(':id', intval($id), PDO::PARAM_INT);
		return $command->queryScalar();
	}

    /**
     * Проверяет целостность размещения (порядок сортировки) блоков
     * @return array результаты проверки
     */
    public static function checkIntegrity()
    {
		$sql = 'SELECT `page_id`, `area`, MIN(`order`) as `min`, MAX(`order`) as `max` ,COUNT(`order`) as `count`
                FROM `' . self::tableName() . '`
                GROUP BY `page_id`, `area`
                ORDER BY `page_id`, `area`
                ';
		$result = Yii::app()->db->createCommand($sql)->queryAll();
        $prevPageId = -1;
        $prevArea = '';
        $prevOrder = -1;
        $ret = array();
        foreach ($result as $row) {
            if (($row['min'] > 0) || ($row['max'] != $row['count']-1)) $ret[] = $row['page_id'] . '=' . $row['area'];
        }
        $percents = round((count($ret) / count($result))*100);
        return array('errors' => $ret, 'percents' => $percents);
        
    }

}