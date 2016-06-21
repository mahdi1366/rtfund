<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.08
//---------------------------

class management_extra_bylaw extends PdoDataAccess
{
	 public $bylaw_id;
	 public $from_date;
	 public $to_date;
	 public $description;

	function  __construct()
	{
		$this->DT_from_date = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_to_date = DataMember::CreateDMA(DataMember::DT_DATE);
	}

	function ADD()
	{
	 	if( parent::insert("management_extra_bylaw", $this) === false )
			return false;

		$this->bylaw_id = parent::InsertID();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = PdoDataAccess::InsertID();
		$daObj->TableName = "management_extra_bylaw";
		$daObj->execute();

		return true;

	}

	function Edit()
	{
	 	$whereParams = array();
	 	$whereParams[":bylaw_id"] = $this->bylaw_id;

	 	if( parent::update("management_extra_bylaw",$this," bylaw_id=:bylaw_id", $whereParams) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->bylaw_id;
		$daObj->TableName = "management_extra_bylaw";
		$daObj->execute();

	 	return true;
    }

	static function GetAll($where = "",$whereParam = array())
	{
		$query = "select bylaw_id,from_date,to_date,description from management_extra_bylaw";
		$query .= ($where != "") ? " where " . $where : "";

		return parent::runquery($query, $whereParam);
	}

	static function Remove($bylaw_id)
	{
	 	$whereParams = array();
	 	$whereParams[":bylaw_id"] = $bylaw_id;

		 if( parent::delete("management_extra_bylaw","  bylaw_id=:bylaw_id", $whereParams) === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $bylaw_id;
		$daObj->TableName = "management_extra_bylaw";
		$daObj->execute();

	 	return true;

	 }
}

class management_extra_bylaw_items extends PdoDataAccess
{
	 public $bylaw_id;
	 public $post_id;
	 public $value;

	 function ReplaceItem()
	 {
		 if(parent::replace("managmnt_extra_bylaw_items", $this) === false )
			return false;
	
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_replace;
		$daObj->MainObjectID = $this->bylaw_id;
		$daObj->SubObjectID = $this->post_id;
		$daObj->TableName = "managmnt_extra_bylaw_items";
		$daObj->execute();

		return true;
	 }

	function ADD()
	{
	 	if( parent::insert("managmnt_extra_bylaw_items", $this) === false )
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->bylaw_id;
		$daObj->SubObjectID = $this->post_id;
		$daObj->TableName = "managmnt_extra_bylaw_items";
		$daObj->execute();

		return true;

	}

	function Edit()
	{
	 	$whereParams = array();
	 	$whereParams[":bylaw_id"] = $this->bylaw_id;
		$whereParams[":postid"] = $this->post_id;

		if( parent::update("managmnt_extra_bylaw_items",$this," bylaw_id=:bylaw_id AND post_id=:postid", $whereParams) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->bylaw_id;
		$daObj->SubObjectID = $this->post_id;
		$daObj->TableName = "managmnt_extra_bylaw_items";
		$daObj->execute();

	 	return true;
    }

	static function GetAll($where = "",$whereParam = array())
	{
		$query = "select i.*,concat(if(p.post_no is null,'',p.post_no),'-',p.title) as post_title, if(p.included = 1,'*','')as included
			from managmnt_extra_bylaw_items i join position p using(post_id)";

		$query .= ($where != "") ? " where " . $where : "";
		
		return parent::runquery($query, $whereParam);
	}

	static function Remove($bylaw_id, $post_id)
	{
	 	$whereParams = array();
	 	$whereParams[":bylaw_id"] = $bylaw_id;
		$whereParams[":postid"] = $post_id;

		 if( parent::delete("managmnt_extra_bylaw_items","  bylaw_id=:bylaw_id AND post_id=:postid", $whereParams) === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $bylaw_id;
		$daObj->SubObjectID = $post_id;
		$daObj->TableName = "managmnt_extra_bylaw_items";
		$daObj->execute();

	 	return true;

	 }
}
?>