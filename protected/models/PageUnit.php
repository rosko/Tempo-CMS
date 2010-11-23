<?php

class PageUnit extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'pages_units';
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
			'page_id' => 'Страница',
//			'unit_id' => 'Unit',
			'area' => 'Область страницы',
//			'order' => 'Order',
		);
	}
	
	/**
     * Возвращает идентификатор блока которому принадлежит pageunit
     * @param integer идентификатор pageunit`а
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
        $prev_page_id = -1;
        $prev_area = '';
        $prev_order = -1;
        $ret = array();
        foreach ($result as $row) {
            if (($row['min'] > 0) || ($row['max'] != $row['count']-1)) $ret[] = $row['page_id'] . '=' . $row['area'];
        }
        $percents = round((count($ret) / count($result))*100);
        return array('errors' => $ret, 'percents' => $percents);
        
    }

}