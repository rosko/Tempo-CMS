<?php
class AuthManager extends CDbAuthManager
{
    private $_cItemChild;
    private $_cAssignment;
    private $_cItem;
    
	protected function checkAccessRecursive($itemName,$userId,$params,$assignments)
	{
		if(($item=$this->getAuthItem($itemName))===null)
			return false;
		Yii::trace('Checking permission "'.$item->getName().'"','system.web.auth.CDbAuthManager');
		if($this->executeBizRule($item->getBizRule(),$params,$item->getData()))
		{
			if(in_array($itemName,$this->defaultRoles))
				return true;
			if(isset($assignments[$itemName]))
			{
				$assignment=$assignments[$itemName];
				if($this->executeBizRule($assignment->getBizRule(),$params,$assignment->getData()))
					return true;
			}
            if (!isset($this->_cItemChild[$itemName])) {
                $sql="SELECT parent FROM {$this->itemChildTable} WHERE child=:name";
                $this->_cItemChild[$itemName] = $this->db->createCommand($sql)->bindValue(':name',$itemName)->queryColumn();
            } 
			foreach($this->_cItemChild[$itemName] as $parent)
			{
				if($this->checkAccessRecursive($parent,$userId,$params,$assignments))
					return true;
			}
		}
		return false;
	}

	public function getAuthAssignments($userId)
	{
        if (!isset($this->_cAssignment[$userId])) {
            $sql="SELECT * FROM {$this->assignmentTable} WHERE userid=:userid";
            $command=$this->db->createCommand($sql);
            $command->bindValue(':userid',$userId);
            $this->_cAssignment[$userId] = $command->queryAll($sql);
        }
		$assignments=array();
		foreach($this->_cAssignment[$userId] as $row)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			$assignments[$row['itemname']]=new CAuthAssignment($this,$row['itemname'],$row['userid'],$row['bizrule'],$data);
		}
		return $assignments;
	}

	public function getAuthItem($name)
	{
        if (!isset($this->_cItem[$name])) {
            $sql="SELECT * FROM {$this->itemTable} WHERE name=:name";
            $command=$this->db->createCommand($sql);
            $command->bindValue(':name',$name);
            $this->_cItem[$name]=$command->queryRow();
        }
		if(($row=$this->_cItem[$name])!==false)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			return new CAuthItem($this,$row['name'],$row['type'],$row['description'],$row['bizrule'],$data);
		}
		else
			return null;
	}
}