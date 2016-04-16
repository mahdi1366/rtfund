<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

class LON_requests extends PdoDataAccess
{
	public $RequestID;
	public $BranchID;
	public $LoanID;
	public $ReqPersonID;
	public $ReqDate;
	public $ReqAmount;
	public $StatusID;
	public $ReqDetails;
	public $BorrowerDesc;
	public $BorrowerID;
	public $LoanPersonID;
	public $guarantees;
	public $AgentGuarantee;
	public $DocumentDesc;	
	public $IsEnded;
	public $SubAgentID;
	
	public $_LoanDesc;
	public $_LoanPersonFullname;
	
	function __construct($RequestID = "") {
		
		if($RequestID != "")
			PdoDataAccess::FillObject ($this, "
				select r.* , concat_ws(' ',fname,lname,CompanyName) _LoanPersonFullname, LoanDesc _LoanDesc
					from LON_requests r 
					left join BSC_persons on(PersonID=LoanPersonID)
					left join LON_loans using(LoanID)
				where RequestID=?", array($RequestID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery_fetchMode("
			select r.*,l.*,
				concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) ReqFullname,
				concat_ws(' ',p2.fname,p2.lname,p2.CompanyName) LoanFullname,
				bi.InfoDesc StatusDesc,
				BranchName
			from LON_requests r
			left join LON_loans l using(LoanID)
			join BSC_branches using(BranchID)
			join BaseInfo bi on(bi.TypeID=5 AND bi.InfoID=StatusID)
			left join BSC_persons p1 on(p1.PersonID=r.ReqPersonID)
			left join BSC_persons p2 on(p2.PersonID=r.LoanPersonID)
			where " . $where, $param);
	}
	
	function AddRequest($pdo = null){
		$this->ReqDate = PDONOW;
		
	 	if(!parent::insert("LON_requests",$this, $pdo))
			return false;
		$this->RequestID = parent::InsertID($pdo);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->RequestID;
		$daObj->TableName = "LON_requests";
		$daObj->execute($pdo);
		return true;
	}
	
	function EditRequest($pdo = null){
		
	 	if( parent::update("LON_requests",$this," RequestID=:l", array(":l" => $this->RequestID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->RequestID;
		$daObj->TableName = "LON_requests";
		$daObj->execute($pdo);
	 	return true;
    }
	
	static function DeleteRequest($RequestID){
		
		$obj = new LON_requests($RequestID);
		if($obj->StatusID != "1")
		{
			ExceptionHandler::PushException("درخواست در حال گردش قابل حذف نمی باشد");
			return false;
		}
		
		if( parent::delete("LON_ReqParts"," RequestID=?", array($RequestID)) === false )
		{
			ExceptionHandler::PushException("خطا در حذف مرحله");
	 		return false;
		}
		if( parent::delete("LON_requests"," RequestID=?", array($RequestID)) === false )
	 	{
			ExceptionHandler::PushException("خطا در حذف درخواست");
			return false;
		}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $RequestID;
		$daObj->TableName = "LON_requests";
		$daObj->execute();
	 	return true;
	}
}

class LON_ReqParts extends PdoDataAccess
{
	public $PartID;
	public $RequestID;
	public $PartDesc;
	public $PartDate;
	public $PartAmount;
	public $InstallmentCount;
	public $IntervalType;
	public $PayInterval;
	public $DelayMonths;
	public $ForfeitPercent;
	public $CustomerWage;
	public $FundWage;
	public $WageReturn;
	public $PayCompute;
	public $MaxFundWage;
	
	function __construct($PartID = "") {
		
		$this->DT_PartDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_MaxFundWage = DataMember::CreateDMA(DataMember::DT_INT, 0);
		
		if($PartID != "")
			PdoDataAccess::FillObject ($this, "select * from LON_ReqParts where PartID=?", array($PartID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select rp.*,r.StatusID,r.LoanPersonID, r.imp_VamCode
			from LON_ReqParts rp join LON_requests r using(RequestID)
			where " . $where, $param);
	}
	
	function AddPart($pdo = null){
		
		if (!parent::insert("LON_ReqParts", $this, $pdo)) {
			return false;
		}
		$this->PartID = parent::InsertID($pdo);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->PartID;
		$daObj->TableName = "LON_ReqParts";
		$daObj->execute($pdo);
		return true;
	}
	
	function EditPart($pdo = null){
		
	 	if( parent::update("LON_ReqParts",$this," PartID=:l", array(":l" => $this->PartID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->PartID;
		$daObj->TableName = "LON_ReqParts";
		$daObj->execute($pdo);
	 	return true;
    }
	
	static function DeletePart($PartID){
		
		if( parent::delete("LON_ReqParts"," PartID=?", array($PartID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $PartID;
		$daObj->TableName = "LON_ReqParts";
		$daObj->execute();
	 	return true;
	}
}

class LON_installments extends PdoDataAccess
{
	public $InstallmentID;
	public $PartID;
	public $InstallmentDate;
	public $InstallmentAmount;
			
	function __construct($InstallmentID = "") {
		
		$this->DT_InstallmentDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_PaidDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($InstallmentID != "")
			PdoDataAccess::FillObject ($this, "select * from LON_installments where InstallmentID=?", array($InstallmentID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select p.*,rp.*
			from LON_installments p
			join LON_ReqParts rp using(PartID)
			where " . $where, $param);
	}
	
	function AddInstallment(){
		
	 	if(!parent::insert("LON_installments",$this))
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->InstallmentID;
		$daObj->TableName = "LON_installments";
		$daObj->execute();
	 	return true;
    }
	
	function EditInstallment($pdo = null){
		
	 	if( parent::update("LON_installments",$this," InstallmentID=:l", 
				array(":l" => $this->InstallmentID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->InstallmentID;
		$daObj->TableName = "LON_installments";
		$daObj->execute($pdo);
	 	return true;
    }
}

class LON_pays extends PdoDataAccess
{
	public $PayID;
	public $PartID;
	public $PayType;
	public $PayDate;
	public $PayAmount;
	public $PayRefNo;
	public $PayBillNo;
	public $ChequeNo;
	public $ChequeBank;
	public $ChequeBranch;
	public $ChequeStatus;
	public $details;
	
	public $_RequestID;
			
	function __construct($PayID = "") {
		
		$this->DT_PayDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($PayID != "")
			PdoDataAccess::FillObject ($this, "
				select p.*,RequestID _RequestID 
				from LON_pays p join LON_ReqParts using(PartID) 
				where PayID=?", array($PayID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select p.*,rp.*,
				b.BankDesc, 
				bi.InfoDesc PayTypeDesc, 
				bi2.InfoDesc ChequeStatusDesc
			from LON_pays p
			left join BaseInfo bi on(bi.TypeID=6 AND bi.InfoID=p.PayType)
			join LON_ReqParts rp using(PartID)
			left join ACC_banks b on(ChequeBank=BankID)
			left join BaseInfo bi2 on(bi2.TypeID=16 AND bi2.InfoID=p.ChequeStatus)
			
			where " . $where, $param);
	}
	
	function AddPay($pdo = null){
		
	 	if(!parent::insert("LON_pays",$this, $pdo))
	 		return false;
		
		$this->PayID = parent::InsertID($pdo);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->PayID;
		$daObj->TableName = "LON_pays";
		$daObj->execute($pdo);
	 	return true;
    }
	
	function EditPay($pdo = null){
		
	 	if( parent::update("LON_pays",$this," PayID=:l", 
				array(":l" => $this->PayID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->PayID;
		$daObj->TableName = "LON_pays";
		$daObj->execute($pdo);
	 	return true;
    }
	
	static function DeletePay($PayID, $pdo = null){
		
		if( parent::delete("LON_pays"," PayID=?", array($PayID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $PayID;
		$daObj->TableName = "LON_pays";
		$daObj->execute($pdo);
	 	return true;
	}
}

?>
