<?php

class PageUnit extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'pages_units';
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'page_id' => 'integer unsigned',
            'unit_id' => 'integer unsigned',
            'area' => 'char(32)',
            'order' => 'integer unsigned',
        );
    }

	public function rules()
	{
		return array(
			array('page_id, unit_id, area, order', 'required'),
			array('page_id, unit_id, order', 'numerical', 'integerOnly'=>true),
			array('area', 'length', 'max'=>32),
		);
	}

	public function relations()
	{
		return array(
			'unit' => array(self::BELONGS_TO, 'Unit', 'unit_id')
		);
	}
	
	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
			'page_id' => Yii::t('cms', 'Page'),
//			'unit_id' => Yii::t('cms', 'Unit'),
			'area' => Yii::t('cms', 'Page area'),
//			'order' => Yii::t('cms', 'Order'),
		);
	}
	
	/**
     * Возвращает идентификатор блока которому принадлежит pageUnit
     * @param integer идентификатор pageUnit`а
     * @return integer  идентификатор блока (Unit)
     */
    public static function getUnitIdById($id)
	{
		$sql = 'SELECT unit_id FROM `' . self::tableName() . '` WHERE id = :id';
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