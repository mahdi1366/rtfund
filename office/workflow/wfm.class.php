<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.08
//-----------------------------

class WFM_flows extends PdoDataAccess {

	public $FlowID;
	public $ObjectType;
	public $FlowDesc;
	public $IsSystemic;

	public $_ObjectType;
	
	function __construct($FlowID = "") {
		if($FlowID != "")
			parent::FillObject ($this, "select f.* , bf.param4 _ObjectType
				from WFM_flows f
				join BaseInfo bf on(bf.TypeID=11 AND f.ObjectType=bf.InfoID)
				where FlowID=?", array($FlowID));
	}
	
	static function GetAll($where = "", $whereParam = array()) {
		
		$query = "select f.*, InfoDesc ObjectDesc,b.param4
			from WFM_flows f
			join BaseInfo b on(TypeID=11 AND ObjectType=InfoID)";
		
		$query .= ($where != "") ? " where " . $where : "";
		
		return parent::runquery_fetchMode($query, $whereParam);
	}

	function AddFlow($pdo = null) {
		
		if (!parent::insert("WFM_flows", $this, $pdo)) {
			return false;
		}

		$this->FlowID = parent::InsertID($pdo);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->FlowID;
		$daObj->TableName = "WFM_flows";
		$daObj->execute($pdo);

		return true;
	}

	function EditFlow($pdo = null) {
		
		if (parent::update("WFM_flows", $this, " FlowID=:fid", array(":fid" => $this->FlowID), $pdo) === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->FlowID;
		$daObj->TableName = "WFM_flows";
		$daObj->execute($pdo);
		
		return true;
	}
	
	static function RemoveFlow($FlowID){
		
		$dt = PdoDataAccess::runquery("select * from WFM_FlowRows where FlowID=?", array($FlowID));
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("این گردش در برخی فرم ها استفاده شده است");
			return false;
		}
		
	 	if(!parent::delete("WFM_flows", " FlowID=?", array($FlowID)))
			return false;
	 	
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $FlowID;
		$daObj->TableName = "WFM_flows";
		$daObj->execute();
		return true;
	}
}

class WFM_FlowSteps extends PdoDataAccess {

	public $StepRowID;
	public $FlowID;
	public $StepID;
	public $StepDesc;
	public $PostID;
	public $PersonID;
	public $JobID;
	public $IsActive;
	public $IsOuter;

	static function GetAll($where = "", $whereParam = array()) {
		
		$query = "select fs.*,PostName,concat_ws(' ',fname, lname,CompanyName) fullname ,
					concat(JobID,'-',PostName) JobDesc
			from WFM_FlowSteps fs
			left join BSC_jobs j using(JobID)
			left join BSC_posts p on(if(fs.PostID is null,j.PostID=p.PostID,fs.PostID=p.PostID))
			left join BSC_persons ps on(ps.PersonID=fs.PersonID)
			";
		$query .= ($where != "") ? " where " . $where : "";
		
		return parent::runquery($query, $whereParam);
	}

	function AddFlowStep($pdo = null) {
		
		if (!parent::insert("WFM_FlowSteps", $this, $pdo)) {
			return false;
		}

		$this->StepRowID = parent::InsertID($pdo);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->StepRowID;
		$daObj->SubObjectID = $this->StepID;
		$daObj->TableName = "WFM_FlowSteps";
		$daObj->execute($pdo);

		return true;
	}

	function EditFlowStep($pdo = null) {
		
		if (parent::update("WFM_FlowSteps", $this, " StepRowID=:srid", 
				array(":srid" => $this->StepRowID), $pdo) === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->StepRowID;
		$daObj->SubObjectID = $this->StepID;
		$daObj->TableName = "WFM_FlowSteps";
		$daObj->execute($pdo);
		
		return true;
	}
	
	static function RemoveFlowStep($StepRowID){
		
		$info = PdoDataAccess::runquery("select * from WFM_FlowSteps where StepRowID=?", array($StepRowID));
		
		$dt = parent::runquery("select * from WFM_FlowRows
			join ( select max(RowID) RowID,FlowID,ObjectID 
					from WFM_FlowRows group by FlowID,ObjectID )t
			using(RowID,FlowID,ObjectID)
			where FlowID=? AND StepRowID=?",array($info[0]["FlowID"], $StepRowID));
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("FlowRowExists");
			return false;
		}
		
		parent::runquery("update WFM_FlowSteps set IsActive='NO', StepID=-1 where StepRowID=?", array($StepRowID));
	
		PdoDataAccess::runquery("update WFM_FlowSteps set StepID=StepID-1 
			where IsOuter='NO' AND StepID>? AND FlowID=?",
			array($info[0]["StepID"], $info[0]["FlowID"]));
	 	
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $StepRowID;
		$daObj->SubObjectID = $info[0]["StepID"];
		$daObj->TableName = "WFM_FlowSteps";
		$daObj->execute();
		return true;
	}
}

class WFM_FlowRows extends PdoDataAccess {

	public $RowID;
	public $FlowID;
	public $StepRowID;
	public $ObjectID;
	public $PersonID;
	public $ActionDate;
	public $ActionType;
	public $ActionComment;
	public $IsEnded;
	public $StepDesc;
	public $IsLastRow;
	
	public $_StepID;
	public $_ObjectType;

	function __construct($RowID = "", $pdo = null) {
		if($RowID != "")
			parent::FillObject ($this, "select f.* ,StepID _StepID, bf.param4 _ObjectType
				from WFM_FlowRows f
				left join WFM_FlowSteps using(StepRowID)
				join WFM_flows fl on(f.FlowID=fl.FlowID)
				join BaseInfo bf on(bf.TypeID=11 AND fl.ObjectType=bf.InfoID)
				
				where RowID=?", array($RowID), $pdo);
	}
	
	static function GetAll($where = "", $whereParam = array()) {
		
		$query = "select sd.*, 
			concat_ws(' ',fname, lname,CompanyName) fullname
			from WFM_FlowRows sd
			join BSC_persons p using(PersonID)";
		
		$query .= ($where != "") ? " where " . $where : "";
		
		return parent::runquery_fetchMode($query, $whereParam);
	}

	function UpdateSourceStatus($StepID){
		
		switch($this->FlowID)
		{
			case "4":
				PdoDataAccess::runquery("update WAR_requests set StatusID=? where RequestID=?",
					array($StepID, $this->ObjectID ));
			case "8":
				PdoDataAccess::runquery("update ACC_docs set StatusID=? where DocID=?",
					array($StepID, $this->ObjectID ));
			case "9":
			case "10":
			case "11":
			case "12":
			case "13":
			case "14":
			case "15":				
				PdoDataAccess::runquery("update ATN_requests set ReqStatus=? where RequestID=?",
					array($StepID, $this->ObjectID ));
		}
	}
	
	function AddFlowRow($StepID , $pdo = null) {
		
		PdoDataAccess::runquery("update WFM_FlowRows set IsLastRow='NO' where FlowID=? AND ObjectID=?", 
				array($this->FlowID, $this->ObjectID), $pdo);
		
		//.......... get StepRowID ...................
		$dt = PdoDataAccess::runquery("select StepRowID, StepDesc from WFM_FlowSteps 
			where IsActive='YES' AND FlowID=? AND StepID=?" , array($this->FlowID, $StepID));
		if(count($dt) == 0)
		{
			ExceptionHandler::PushException("خطا در تعریف وضعیت ها");
			return false;
		}
		$this->StepRowID = $dt[0]["StepRowID"];	
		$this->StepDesc = $dt[0]["StepDesc"];	
		//..............................................
		
		if (!parent::insert("WFM_FlowRows", $this, $pdo)) {
			return false;
		}

		$this->UpdateSourceStatus($StepID);
		
		$this->RowID = parent::InsertID($pdo);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->RowID;
		$daObj->TableName = "WFM_FlowRows";
		$daObj->execute($pdo);

		
		return true;
	}

	static function AddOuterFlow($FlowID, $ObjectID, $StepID, $Comment = "", $pdo = null){
		
		$dt = parent::runquery("select StepRowID from WFM_FlowSteps where FlowID=? AND StepID=?", 
			array($FlowID, $StepID));
		
		if(count($dt) == 0)
			return false;
		
		$obj = new self();
		$obj->FlowID = $FlowID;
		$obj->StepRowID = $dt[0]["StepRowID"];
		$obj->ObjectID = $ObjectID;
		$obj->PersonID = $_SESSION["USER"]["PersonID"];
		$obj->ActionType = "DONE";
		$obj->ActionDate = PDONOW;
		$obj->ActionComment = $Comment;
		return $obj->AddFlowRow($StepID, $pdo);
	}
	
	static function EndObjectFlow($ObjectType, $ObjectID, $pdo = null){
		
		switch($ObjectType)
		{
			case 'contract' : 
				$EndStepID = CNT_STEPID_CONFIRM; 
				PdoDataAccess::runquery("update CNT_contracts set StatusID=? where ContractID=?", 
					array($EndStepID, $ObjectID), $pdo);
				return ExceptionHandler::GetExceptionCount() == 0;
			case 'plan' : 
				$EndStepID = 105; 
				PdoDataAccess::runquery("update PLN_plans set StepID=? where PlanID=?", 
					array($EndStepID, $ObjectID), $pdo);
				return ExceptionHandler::GetExceptionCount() == 0;
			case 'warrenty' : 
				$EndStepID = WAR_STEPID_CONFIRM; 
				PdoDataAccess::runquery("update WAR_requests set StatusID=? where RequestID=?", 
					array($EndStepID, $ObjectID), $pdo);
				return ExceptionHandler::GetExceptionCount() == 0;
			case 'accdoc' : 
				$EndStepID = ACC_STEPID_CONFIRM; 
				PdoDataAccess::runquery("update ACC_docs set StatusID=? where DocID=?", 
					array($EndStepID, $ObjectID), $pdo);
				return ExceptionHandler::GetExceptionCount() == 0;
			case 'CORRECT':
			case 'DayOFF':
			case 'OFF':
			case 'DayMISSION':
			case 'MISSION':
			case 'EXTRA':
			case 'CHANGE_SHIFT':
				$EndStepID = ATN_STEPID_CONFIRM; 
				PdoDataAccess::runquery("update ATN_requests set ReqStatus=? where RequestID=?", 
					array($EndStepID, $ObjectID), $pdo);
				return ExceptionHandler::GetExceptionCount() == 0;
		}
		
		return true;
	}
	
	function EditFlowRow($pdo = null) {
		
		if (parent::update("WFM_FlowRows", $this, " RowID=:did", array(":did" => $this->RowID), $pdo) === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->RowID;
		$daObj->TableName = "WFM_FlowRows";
		$daObj->execute($pdo);
		
		return true;
	}
	
	static function StartFlow($FlowID, $ObjectID){
		
		$obj = new WFM_FlowRows();
		$obj->FlowID = $FlowID;
		$obj->StepRowID = PDONULL;
		$obj->ObjectID = $ObjectID;
		$obj->PersonID = $_SESSION["USER"]["PersonID"];		
		$obj->ActionDate = PDONOW;
		$obj->ActionType = "CONFIRM";
		return $obj->AddFlowRow(0);		
	}
	
	static function ReturnStartFlow($FlowID, $ObjectID, $pdo = null){
		
		$newObj = new WFM_FlowRows();
		$newObj->FlowID = $FlowID;
		$newObj->ObjectID = $ObjectID;
		$newObj->PersonID = $_SESSION["USER"]["PersonID"];
		$newObj->ActionType = "REJECT";
		$newObj->ActionDate = PDONOW;
		$newObj->ActionComment = "برگشت شروع گردش";
		$newObj->AddFlowRow("0", $pdo);	
		
		//-------------------------------------------------------
		
		$FlowObj = new WFM_flows($FlowID);
		switch($FlowObj->_ObjectType)
		{
			case 'contract' : 
				$EndStepID = CNT_STEPID_RAW; 
				PdoDataAccess::runquery("update CNT_contracts set StatusID=? where ContractID=?", 
					array($EndStepID, $ObjectID), $pdo);
				break;
			case 'warrenty' : 
				$EndStepID = WAR_STEPID_RAW; 
				PdoDataAccess::runquery("update WAR_requests set StatusID=? where RequestID=?", 
					array($EndStepID, $ObjectID), $pdo);
				break;
			case 'accdoc' : 
				$EndStepID = ACC_STEPID_RAW; 
				PdoDataAccess::runquery("update ACC_docs set StatusID=? where DocID=?", 
					array($EndStepID, $ObjectID), $pdo);
				break;
			case 'CORRECT':
			case 'DayOFF':
			case 'OFF':
			case 'DayMISSION':
			case 'MISSION':
			case 'EXTRA':
			case 'CHANGE_SHIFT':
				$EndStepID = ATN_STEPID_RAW; 
				PdoDataAccess::runquery("update ATN_requests set ReqStatus=? where RequestID=?", 
					array($EndStepID, $ObjectID), $pdo);
				break;
		}
		return ExceptionHandler::GetExceptionCount() == 0;
		
	}
	
	static function DeleteAllFlow($FlowID, $ObjectID){
		
		switch($FlowID*1)
		{
			case 2 : 
				$RawStepID = 100; 
				PdoDataAccess::runquery("update CNT_contracts set StatusID=? where ContractID=?", array($RawStepID, $ObjectID));
				PdoDataAccess::runquery("delete from WFM_FlowRows where FlowID=? AND ObjectID=?", array($FlowID, $ObjectID));
				return ExceptionHandler::GetExceptionCount() == 0;
			/*case 3 : 
				$RawStepID = 100; 
				PdoDataAccess::runquery("update PLN_plans set StepID=? where PlanID=?", 
					array($RawStepID, $ObjectID), $pdo);
				return ExceptionHandler::GetExceptionCount() == 0;*/
			case 4 : 
				$RawStepID = 100; 
				require_once("../../loan/warrenty/request.class.php");
				$obj = new WAR_requests($ObjectID);
				if($obj->GetAccDoc()*1 > 0)
				{
					ExceptionHandler::PushException("این ضمانت نامه دارای سند بوده و گردش آن قابل حذف نمی باشد");
					return false;
				}
				PdoDataAccess::runquery("delete from WFM_FlowRows where FlowID=? AND ObjectID=?", array($FlowID, $ObjectID));
				PdoDataAccess::runquery("update WAR_requests set StatusID=? where RequestID=?", 
					array($RawStepID, $ObjectID));
				return ExceptionHandler::GetExceptionCount() == 0;
		}
		
		return ExceptionHandler::GetExceptionCount() == 0;
	}
	
	static function IsFlowStarted($FlowID, $ObjectID){
		
		$dt = PdoDataAccess::runquery("select * from WFM_FlowRows "
			. "where FlowID=? AND ObjectID=?", array($FlowID, $ObjectID));
		
		return (count($dt) > 0);
	}
	
	static function IsFlowEnded($FlowID, $ObjectID){
		
		$dt = PdoDataAccess::runquery("select IsEnded from WFM_FlowRows 
			where FlowID=? AND ObjectID=? AND ActionType='CONFIRM'
			order by RowID desc", array($FlowID, $ObjectID));
		
		if(count($dt) > 0 && $dt[0][0] == "YES")
			return true;
		return false;
	}
	
	static function GetFlowInfo($FlowID, $ObjectID){
		
		$dt = PdoDataAccess::runquery("select * from WFM_FlowRows fr
			join WFM_FlowSteps sp on(sp.FlowID=fr.FlowID AND fr.StepRowID=sp.StepRowID)
			where fr.FlowID=? AND ObjectID=? 
			order by RowID desc limit 1", array($FlowID, $ObjectID));
		
		return array(
			"IsStarted" => count($dt) > 0 ? true : false,
			"IsEnded" => count($dt) > 0 && $dt[0]["IsEnded"] == "YES" ? true : false,
			"ResendEnable" => count($dt) > 0 && $dt[0]["ActionType"] == "REJECT" && $dt[0]["StepID"] == "1" ? true : false,
			"JustStarted" => count($dt) > 0 && $dt[0]["StepRowID"] == "" ? true : false ,
			"StepDesc" => count($dt) > 0 ? ($dt[0]["StepDesc"] == "" ? "شروع گردش" : $dt[0]["StepDesc"]) : "خام"
		);
	}
	
	static function SelectReceivedForms($where="", $params = array()){
		
		$dt = PdoDataAccess::runquery("select FlowID,StepID 
			from WFM_FlowSteps s 
			left join BSC_persons p using(PostID)
			where s.IsActive='YES' AND if(s.PersonID>0,s.PersonID=:pid,p.PersonID=:pid)",
				array(":pid" => $_SESSION["USER"]["PersonID"]));
		if(count($dt) == 0)
			return array();

		$where .= " AND fr.IsEnded='NO' AND (";
		foreach($dt as $row)
		{
			$preStep = $row["StepID"]*1-1;
			$nextStep = $row["StepID"]*1+1;

			$where .= "(fr.FlowID=" . $row["FlowID"] .
				" AND fs.StepID" . ($preStep == 0 ? " is null" : "=" . $preStep) . 
				" AND ActionType='CONFIRM') OR (fr.FlowID=" . $row["FlowID"] . 
				" AND fs.StepID=" . $nextStep . " AND ActionType='REJECT') OR";
		}
		$where = substr($where, 0, strlen($where)-2) . ")";
		//--------------------------------------------------------
		$query = "select fr.*,f.FlowDesc, 
						b.InfoDesc ObjectTypeDesc,
						ifnull(fr.StepDesc,'شروع گردش') StepDesc,
						if(p.IsReal='YES',concat(p.fname, ' ',p.lname),p.CompanyName) fullname,
						b.param1 url,
						b.param2 parameter
					from WFM_FlowRows fr
					join ( select max(RowID) RowID,FlowID,ObjectID from WFM_FlowRows group by FlowID,ObjectID )t
						using(RowID,FlowID,ObjectID)
					join WFM_flows f using(FlowID)
					join BaseInfo b on(b.TypeID=11 AND b.InfoID=f.ObjectType)
					left join WFM_FlowSteps fs on(fr.StepRowID=fs.StepRowID)
					join BSC_persons p on(fr.PersonID=p.PersonID)

					where 1=1 " . $where . dataReader::makeOrder();
		return PdoDataAccess::runquery_fetchMode($query, $params);
	}
	
}

class WFM_FlowStepPersons extends OperationClass {

	const TableName = "WFM_FlowStepPersons";
	const TableKey = "RowID"; 
	
	public $RowID;
	public $StepRowID;
	public $PersonID;
	
	static function Get($where = '', $whereParams = array()) {
		
		return parent::runquery_fetchMode("select fp.*, concat_ws(' ',fname,lname,CompanyName) fullname 
			from WFM_FlowStepPersons fp join BSC_persons p using(PersonID)
			where 1=1 " . $where, $whereParams);
	}
}

?>