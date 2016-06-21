<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.11
//---------------------------

class Worker_Job extends PdoDataAccess
{
	public $job_id;
	public $title;
	public $job_group;
	public $conditions;
	public $duties;

	function  __construct($jfid = "")
	{
		
	}

	
	static	function SelectWorkerJobs($where = "",$whereParam = array())
	{
		$query = "select * from jobs";
		if($where != "")
			$query .= " where " . $where;
		
		$temp = parent::runquery($query,$whereParam);
		return $temp;
	
	}

	function Add()
	{
		$this->job_id = PdoDataAccess::GetLastID("`jobs`", "job_id") + 1;
		if(!PdoDataAccess::insert("`jobs`",$this))
			return false ;
			
		$id = PdoDataAccess::InsertID();

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $id;
		$daObj->TableName = "jobs";
		$daObj->execute();
		
		return true ; 
	}
	
	function Edit()
	{
		if(!PdoDataAccess::update("jobs",$this," job_id =:JID" , array(':JID'=>$this->job_id)))
			return false ; 
			
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->job_id;
		$daObj->TableName = "jobs";
		$daObj->execute();

		return true;
	}

	function Remove()
	{
		if(!PdoDataAccess::delete("jobs", "job_id =:JID", array(':JID'=>$this->job_id)))
			return false ; 
			
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $this->job_id;
		$daObj->TableName = "jobs";
		$daObj->execute();
	
		return true ; 
	}
	
	
}
?>