<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------

class be_jobField extends PdoDataAccess
{
	public $jfid;
	public $jcid;
	public $jsid;
	public $grade;
	public $start_group;
	public $title;
	public $conditions;
	public $duties;
	public $educ_research;
	public $job_type;
	public $job_level;

	function  __construct($jfid = "")
	{
		if($jfid == "")
			return;
		
		parent::FillObject($this, "select * from job_fields where jfid=?", array($jfid));

		if(parent::AffectedRows() == 0)
		{
			$this->PushException("کد وارد شده معتبر نمی باشد.");
			return;
		}
	}

	function select($where = "")
	{
		$query = "select * from job_fields";
		
		if($where != "")
			$query .= " where " . $where;
			
		$temp = parent::runquery($query);
		return $temp;
	}

	function Add()
	{
		PdoDataAccess::insert("job_fields", $this);
		$this->jfid = parent::InsertID();

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->jfid;
		$daObj->TableName = "job_fields";
		$daObj->execute();
	}
	
	function Edit()
	{
		if(!PdoDataAccess::update("job_fields", $this, "jfid=:jfid", array(":jfid" => $this->jfid)))
			return false;
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->jfid;
		$daObj->TableName = "job_fields";
		$daObj->execute();

		return true;
	}

	static function Remove($jfid)
	{
		PdoDataAccess::delete("job_fields", "jfid=?", array($jfid));

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $jfid;
		$daObj->TableName = "job_fields";
		$daObj->execute();
	}
}
?>