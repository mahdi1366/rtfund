<?php
//---------------------------
// programmer:	bMahdipour
// create Date:	90.12.16
//---------------------------

class manage_study_fields extends PdoDataAccess
{
	 
	public $sfid;
	public $ptitle;

	
	function __construct()
	 {
	 	
	 }
	static function GetAll($where = "",$whereParam = array())
	{
		$query = "select * from study_fields";
		$query .= ($where != "") ? " where " . $where : "";
		return parent::runquery($query, $whereParam);
	}
	 
	function Add()
	{
	 	if( parent::insert("study_fields", $this) === false )
			return false;

		$this->sfid = parent::InsertID();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = PdoDataAccess::InsertID();
		$daObj->TableName = "study_fields";
		$daObj->execute();

		return true;
	}
	function Edit()
	{
	 	$whereParams = array();
	 	$whereParams[":sfid"] = $this->sfid;

	 	if( parent::update("study_fields",$this," sfid=:sfid", $whereParams) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->sfid;
		$daObj->TableName = "study_fields";
		$daObj->execute();

	 	return true;
    }	
	 	 

	static function Remove($sfid)
	{

        $res = parent::runquery(" select count(*) cn from study_branchs where sfid = ".$sfid );

        if($res[0]['cn'] > 0 )
        {
            parent::PushException("برای این رشته گرایش تعریف شده است به همین دلیل حذف امکان پذیر نمی باشد .");
            return false ;
        }

        $educRes = parent::runquery(" select count(*) cn from person_educations where sfid = ".$sfid );

        if($educRes[0]['cn'] > 0 )
        {
            parent::PushException("این رشته در اطلاعات پایه افراد ثبت شده است به همین دلیل حذف امکان پذیر نمی باشد .");
            return false ;
        }

        $result = parent::delete("study_fields", "sfid=:sfid ", array(":sfid" => $sfid));
		
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $sfid;
		$daObj->TableName = "study_fields";
		$daObj->execute();

		return true;
	}
}
	




?>