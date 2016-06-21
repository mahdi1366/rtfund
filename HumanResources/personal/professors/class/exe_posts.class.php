<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.06
//---------------------------
require_once inc_manage_staff;

class manage_professor_exe_posts extends PdoDataAccess
{
	 public $staff_id;
	 public $row_no;
	 public $post_id;
	 public $letter_no;
	 public $letter_date;
	 public $from_date;
	 public $to_date;
	 public $description;

	function  __construct()
	{
		$this->DT_letter_date = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_from_date = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_to_date = DataMember::CreateDMA(DataMember::DT_DATE);
	}
     	 
	function ADD()
	{
	 	$this->row_no = (self::LastID($this->staff_id)+1);
	 	if( parent::insert("professor_exe_posts", $this) === false )
			return false;
//echo parent::GetLatestQueryString(); die();
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $this->staff_id;
		$daObj->MainObjectID = $this->staff_id;
		$daObj->SubObjectID = $this->row_no;
		$daObj->TableName = "professor_exe_posts";
		$daObj->execute();

		return true;	

	}

	function assign_post()
	{
		$temp = parent::runquery("SELECT staff_id FROM position WHERE post_id = ?",array($this->post_id));
		if($temp[0]["staff_id"] != "" && $temp[0]["staff_id"] != $this->staff_id)
		{
			ExceptionHandler::PushException(str_replace("%0%", $temp[0]["staff_id"], POST_HAS_DETERMINED_ERR));
			return false;
		}
		parent::runquery("update position set staff_id=" . $this->staff_id . " where post_id=" . $this->post_id);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $this->staff_id;
		$daObj->MainObjectID = $this->post_id;
		$daObj->TableName = "position";
		$daObj->description = "انتساب پست اجرایی به هیئت علمی";
		$daObj->execute();

		//------------------ baseinfo update ---------------------------
		$staffObj = new manage_staff("", "", $this->staff_id);
		require_once inc_manage_post;
		manage_posts::baseinfoAssign($staffObj->PersonID, $this->post_id, date("Y-m-d"), "انتساب پست اجرایی به هیئت علمی");
		//--------------------------------------------------------------

		return true;
	}

	function release_post()
	{
		$temp = parent::runquery("SELECT s.staff_id, s.post_id
    		FROM position p INNER JOIN staff s ON(s.staff_id = p.staff_id)
    		WHERE p.post_id = ? AND s.staff_id=?",array($this->post_id, $this->staff_id));

		if(count($temp) == 0)
			return true;
		
		if($temp[0]["post_id"] == $this->post_id)
		{
			ExceptionHandler::PushException(CANNT_RELEASE_WRIT_POST);
			return false;
		}
		parent::runquery("update position set staff_id=null where post_id=" . $this->post_id);
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $this->staff_id;
		$daObj->MainObjectID = $this->post_id;
		$daObj->TableName = "position";
		$daObj->description = "آزاد کردن پست اجرایی هیئت علمی";
		$daObj->execute();
		
		//------------------ baseinfo update ---------------------------
		$staffObj = new manage_staff("", "", $this->staff_id);
		require_once inc_manage_post;
		manage_posts::baseinfoRelease($staffObj->PersonID, $this->post_id, "آزاد کردن پست اجرایی هیئت علمی");
		//--------------------------------------------------------------
		
		return true;
	}
	 
	function Edit()
	{ 
	 	$whereParams = array();
	 	$whereParams[":staff_id"] = $this->staff_id;
	 	$whereParams[":rowid"] = $this->row_no;
	 	
	 	if( parent::update("professor_exe_posts",$this," staff_id=:staff_id and row_no=:rowid ", $whereParams) === false )
	 		return false;
	 
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $this->staff_id;
		$daObj->MainObjectID = $this->staff_id;
		$daObj->SubObjectID = $this->row_no;
		$daObj->TableName = "professor_exe_posts";
		$daObj->execute();
		
	 	return true;
    }

	static function GetAll($where = "",$whereParam = array())
	{ 
		$query = " select pe.*,p.title as postTitle,if(p.staff_id=pe.staff_id, 1, 0) as assign_post
				from professor_exe_posts pe
					LEFT JOIN position p using(post_id)
				where p.person_type in(" . manage_access::getValidPersonTypes() . ")";
		$query .= ($where != "") ? " AND " . $where : "";
		
		$temp = parent::runquery($query, $whereParam);
		return $temp;
	}
	 		
	private static function LastID($staff_id)
	{
	 	$whereParam = array();
	 	$whereParam[":staff_id"] = $staff_id;
	 	
	 	return parent::GetLastID("professor_exe_posts","row_no","staff_id=:staff_id",$whereParam);
	}
	 
	static function Remove($staff_id, $row_no)
	{
	 	$whereParams = array();
	 	$whereParams[":staff_id"] = $staff_id;
	 	$whereParams[":rowid"] = $row_no;
	 	 	
		 if( parent::delete("professor_exe_posts"," staff_id=:staff_id and row_no=:rowid", $whereParams) === false) {
			parent::PushException(ER_PERSON_DEP_DEL);
	 		return false;	 	
	 	}
	 	
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $staff_id;
		$daObj->MainObjectID = $staff_id;
		$daObj->SubObjectID = $row_no;
		$daObj->TableName = "professor_exe_posts";
		$daObj->execute();

	 	return true;
	 		 	
	 }
}


?>
