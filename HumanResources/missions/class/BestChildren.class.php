<?php

//-----------------------------
//	Programmer	: B.Mahdipour
//	Date		: 94.05
//-----------------------------
require_once '../../header.inc.php';

class manageBestChild extends PdoDataAccess {

	public $BSID;
	public $PersonID;
	public $CFName;
	public $CLName;
	public $sex;
	public $EducLevel;
	public $EducBase;
	public $grade;
	public $PicFileType;
	public $PaperFileType;
	public $RegDate;
	public $status;
	public $comments;
	public $NationalCode;

	function __construct() {
		return;
	}

	public static function IsValidNID($NID = null) {

		if ($NID == '' || !preg_match("/^[0-9]{10}$/", $NID)) {
			return "false";
		}
		$NID_DIG = str_split($NID);
		$NID_SUM = 0;
		for ($i = 10; $i > 1; $i--) {
			$NID_SUM +=$i * $NID_DIG[10 - $i];
		}
		$CH_SUM = ($NID_SUM % 11);
		$CH_SUM = $CH_SUM < 2 ? $CH_SUM : 11 - $CH_SUM;
		if ($CH_SUM != $NID_DIG[9])
			return "false";
		return true;
	}

	function GetChildItm($whr) {
		$query = " select * from hrmstotal.BestStudent " . $whr;

		return parent::runquery($query);
	}

	function ADD() {

		if (!parent::insert("hrmstotal.BestStudent", $this)) {

			print_r(ExceptionHandler::PopAllExceptions());
			die();
			return false;
		}

		$this->BSID = parent::InsertID();
		parent::audit('ثبت درخواست با شناسه '
				. $this->BSID);
		return true;
	}

	function Edit() {

		$result = parent::update("hrmstotal.BestStudent", $this, "BSID=" . $this->BSID);

		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->BSID;
		$daObj->TableName = "BestStudent";
		$daObj->execute();

		return true;
	}

	public static function Remove($BSID) {
		$query = " select count(*) cn 
				   from hrmstotal.BestStudent 
				   where status !=0 AND BSID = " . $BSID;
		$res = parent::runquery($query);

		if ($res[0]['cn'] > 0) {
			parent::PushException(" این درخواست بررسی گردیده است و حذف آن امکان پذیر نمی باشد.");
			return false;
		}

		$result = parent::delete("hrmstotal.BestStudent", "BSID=:BID ", array(":BID" => $BSID));

		$rec = PdoDataAccess::runquery("select PicFileType from hrmstotal.BestStudent where BSID = ?", array($BSID));
		$extension = $rec[0]['PicFileType'];
		$FileName = "/mystorage/BestStuDocument/PicDoc/" . $BSID . "." . $extension;
		unlink($FileName);

		$rec = PdoDataAccess::runquery("select PaperFileType from hrmstotal.BestStudent where BSID = ?", array($BSID));
		$extension = $rec[0]['PaperFileType'];
		$FileName = "/mystorage/BestStuDocument/PicDoc/" . $BSID . "." . $extension;
		unlink($FileName);

		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $BSID;
		$daObj->TableName = "BestStudent";
		$daObj->execute();

		return true;
	}

}