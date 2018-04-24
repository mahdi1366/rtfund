<?php
//---------------------------
// programmer:	bMahdipour
// create Date:	95.7
//---------------------------

class manage_study_branch extends PdoDataAccess
{
	 
	public $sfid;
	public $sbid;
    public $ptitle;
    public $etitle;

	
	function __construct()
	 {
	 	
	 }
	static function GetAll($where = "",$whereParam = array())
	{
		$query = "select * from HRM_study_branchs";
		$query .= ($where != "") ? " where " . $where : "";
		return parent::runquery($query, $whereParam);
	}
	 

	function Add()
	{
        $this->sbid = (manage_study_branch::LastID($this->sfid)+1);
        
        if( parent::insert("HRM_study_branchs", $this) === false )
			return false;

		$this->sfid = parent::InsertID();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = PdoDataAccess::InsertID();
		$daObj->TableName = "HRM_study_branchs";
		$daObj->execute();

		return true;
	}
	function Edit()
	{
	 	$whereParams = array();
	 	$whereParams[":sfid"] = $this->sfid;
        $whereParams[":sbid"] = $this->sbid;

	 	if( parent::update("HRM_study_branchs",$this," sfid=:sfid and sbid=:sbid ", $whereParams) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->sfid;
        $daObj->SubObjectID = $this->sbid;
		$daObj->TableName = "HRM_study_branchs";
		$daObj->execute();

	 	return true;
    }
	 	 
	private static function LastID($sfid)
	{
	 	$whereParam = array();
	 	$whereParam[":sfid"] = $sfid;
	 	
	 	return parent::getLastID("HRM_study_branchs", "sbid", "sfid=:sfid", $whereParam);
	}

	static function Remove($sfid,$sbid)
	{

        $res = parent::runquery(" select count(*) cn from HRM_person_educations where sfid = ".$sfid." and sbid = ".$sbid );

        if($res[0]['cn'] > 0 )
        {
             parent::PushException("این رشته در اطلاعات پایه افراد ثبت شده است به همین دلیل حذف امکان پذیر نمی باشد .");
            return false ;
        }
        
        $result = parent::delete("HRM_study_branchs", "sfid=:sfid and sbid=:sbid", array(
                                                  ":sfid" => $sfid , ":sbid" => $sbid ));
		
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $sfid;
        $daObj->SubObjectID = $sbid;
		$daObj->TableName = "HRM_study_branchs";
		$daObj->execute();

		return true;
	}
}
	




?>