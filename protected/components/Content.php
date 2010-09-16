<?php
class Content extends CActiveRecord
{
	public static function form()
	{
		return array();
    }

    public function getUnit()
    {
		return Unit::model()->find('id=:id', array(':id'=>$this->unit_id));        
    }

    public function dependencies()
    {
        return array();
    }
    
    public function page($number, $per_page=null)
    {
        if (!$per_page)
            $per_page = Yi::app()->params['defaults']['perPage'];
        
        $offset = ($per_page-1)*$per_page;
        if ($offset < 0)
            $offset = 0;
        $this->getDbCriteria()->mergeWith(array(
            'limit'=>$per_page,
            'offset'=>$offset
        ));
        return $this;        
    }
}

?>