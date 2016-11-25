<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

require_once getenv("DOCUMENT_ROOT") . '/office/dms/dms.class.php';

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
	public $IsFree;
	public $imp_VamCode;
	
	public $_LoanDesc;
	public $_LoanPersonFullname;
	public $_ReqPersonFullname;
	public $_BranchName;
	
	function __construct($RequestID = "") {
		
		if($RequestID != "")
			PdoDataAccess::FillObject ($this, "
				select r.* , concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) _LoanPersonFullname, LoanDesc _LoanDesc,
						concat_ws(' ',p2.fname,p2.lname,p2.CompanyName) _ReqPersonFullname, b.BranchName _BranchName
						
					from LON_requests r 
					left join BSC_persons p1 on(p1.PersonID=LoanPersonID)
					left join LON_loans using(LoanID)
					left join BSC_persons p2 on(p2.PersonID=ReqPersonID)
					left join BSC_branches b using(BranchID)
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
			left join BaseInfo bi on(bi.TypeID=5 AND bi.InfoID=StatusID)
			left join BSC_persons p1 on(p1.PersonID=r.ReqPersonID)
			left join BSC_persons p2 on(p2.PersonID=r.LoanPersonID)
			where " . $where, $param);
	}
	
	function CheckForDuplicate(){
		
		if(!empty($this->RequestID))
		{
			$dt = PdoDataAccess::runquery("
			select r2.RequestID from LON_requests r1
				join LON_requests r2 on(r1.RequestID<>r2.RequestID 
					AND substr(r1.ReqDate,1,10)=substr(r2.ReqDate,1,10) AND r1.ReqAmount=r2.ReqAmount 
					AND (if(r1.LoanPersonID>0,r1.LoanPersonID=r2.LoanPersonID,1=0) 
						OR if(r1.BorrowerID<>'',r1.BorrowerID=r2.BorrowerID,1=0) 
						OR if(r1.BorrowerDesc<>'',r1.BorrowerDesc=r2.BorrowerDesc,1=0) ) )
			where r1.RequestID=?",array($this->RequestID));

			if(count($dt) > 0)
			{
				ExceptionHandler::PushException("در این تاریخ و با این مبلغ وام دیگری با شماره" .
					$dt[0][0]. " ثبت شده است ");
				return false;
			}
		}
		else
		{
			$dt = PdoDataAccess::runquery("
			select r2.RequestID from LON_requests r2 
			where substr(r2.ReqDate,1,10)=substr(now(),1,10) 
				AND r2.ReqAmount=?
				AND (r2.LoanPersonID=? OR r2.BorrowerID=? OR r2.BorrowerDesc=?)",
				array($this->ReqAmount, $this->LoanPersonID, $this->BorrowerID, $this->BorrowerDesc));
			if(count($dt) > 0)
			{
				ExceptionHandler::PushException("در این تاریخ و با این مبلغ وام دیگری با شماره" .
					$dt[0][0]. " ثبت شده است ");
				return false;
			}
		}
		return true;
	}
	
	function AddRequest($pdo = null){
		$this->ReqDate = PDONOW;
		
		if(!$this->CheckForDuplicate())
			return false;
		
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
		
		if(!$this->CheckForDuplicate())
			return false;
		
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
		
		if(!DMS_documents::DeleteAllDocument($RequestID, "loan"))
		{
			ExceptionHandler::PushException("خطا در حذف مدارک");
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
	public $PartStartDate;
	public $PartAmount;
	public $InstallmentCount;
	public $IntervalType;
	public $PayInterval;
	public $DelayMonths;
	public $DelayDays;
	public $ForfeitPercent;
	public $DelayPercent;
	public $CustomerWage;
	public $FundWage;
	public $WageReturn;
	public $PayCompute;
	public $MaxFundWage;
	public $DelayReturn;
	public $AgentReturn;
	public $AgentDelayReturn;
	public $IsHistory;
	
	function __construct($PartID = "") {
		
		$this->DT_PartDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_PartStartDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_MaxFundWage = DataMember::CreateDMA(DataMember::DT_INT, 0);
		
		if($PartID != "")
			PdoDataAccess::FillObject ($this, "select * from LON_ReqParts where PartID=?", array($PartID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select rp.*,r.StatusID,r.LoanPersonID,r.ReqPersonID, r.imp_VamCode
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
	
	static function DeletePart($PartID, $pdo = null){
		
		if( parent::delete("LON_ReqParts"," PartID=?", array($PartID),$pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $PartID;
		$daObj->TableName = "LON_ReqParts";
		$daObj->execute($pdo);
	 	return true;
	}
	
	static function GetValidPartObj($RequestID){
		
		$dt = PdoDataAccess::runquery("select * from LON_ReqParts 
			where IsHistory='NO' AND RequestID=? order by PartID desc limit 1",array($RequestID));
		if(count($dt) == 0)
			return null;
		
		return new LON_ReqParts($dt[0]["PartID"]);
	}
}

class LON_installments extends PdoDataAccess
{
	public $InstallmentID;
	public $RequestID;
	public $InstallmentDate;
	public $InstallmentAmount;
	public $IsDelayed;
			
	function __construct($InstallmentID = "") {
		
		$this->DT_InstallmentDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_PaidDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($InstallmentID != "")
			PdoDataAccess::FillObject ($this, "select * from LON_installments where InstallmentID=?", array($InstallmentID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select * from LON_installments i
			join LON_requests r using(RequestID)
			join LON_ReqParts p on(r.RequestID=p.RequestID AND p.IsHistory='NO')
			where " . $where, $param);
	}
	
	function AddInstallment($pdo = null){
		
	 	if(!parent::insert("LON_installments",$this, $pdo))
	 		return false;

		$this->InstallmentID = parent::InsertID($pdo);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->InstallmentID;
		$daObj->TableName = "LON_installments";
		$daObj->execute($pdo);
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

class LON_BackPays extends PdoDataAccess
{
	public $BackPayID;
	public $RequestID;
	public $PayType;
	public $PayDate;
	public $PayAmount;
	public $PayRefNo;
	public $PayBillNo;
	public $details;
	public $IsGroup;
	public $IncomeChequeID;
	public $EqualizationID;
	
	function __construct($BackPayID = "") {
		
		$this->DT_PayDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($BackPayID != "")
			PdoDataAccess::FillObject ($this, "
				select *
				from LON_BackPays 
				where BackPayID=?", array($BackPayID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		$temp = preg_split("/order by/", $where);
		$where = $temp[0];
		$order = count($temp) > 1 ? " order by " . $temp[1] : "";
		
		return PdoDataAccess::runquery("
			select p.*,
				i.ChequeNo,
				t.TafsiliDesc ChequeStatusDesc,
				bi.InfoDesc PayTypeDesc, 				
				d.LocalNo,
				d.DocStatus
			from LON_BackPays p
			left join BaseInfo bi on(bi.TypeID=6 AND bi.InfoID=p.PayType)
			left join ACC_IncomeCheques i using(IncomeChequeID)
			left join ACC_tafsilis t on(t.TafsiliType=".TAFTYPE_ChequeStatus." AND t.TafsiliID=ChequeStatus)
			
			left join ACC_DocItems di on(SourceID=RequestID AND SourceID2=BackPayID AND SourceType in(8,5))
			left join ACC_docs d on(di.DocID=d.DocID)
			
			where " . $where . " group by BackPayID " . $order, $param);
	}
	
	function Add($pdo = null){
		
	 	if(!parent::insert("LON_BackPays",$this, $pdo))
	 		return false;
		
		$this->BackPayID = parent::InsertID($pdo);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->BackPayID;
		$daObj->TableName = "LON_BackPays";
		$daObj->execute($pdo);
	 	return true;
    }
	
	function Edit($pdo = null){
		
	 	if( parent::update("LON_BackPays",$this," BackPayID=:l", 
				array(":l" => $this->BackPayID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->BackPayID;
		$daObj->TableName = "LON_BackPays";
		$daObj->execute($pdo);
	 	return true;
    }
	
	static function DeletePay($BackPayID, $pdo = null){
		
		if( parent::delete("LON_BackPays"," BackPayID=?", array($BackPayID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $BackPayID;
		$daObj->TableName = "LON_BackPays";
		$daObj->execute($pdo);
	 	return true;
	}
	
	static function GetAccDoc($BackPayID, $pdo = null){
		
		$obj = new LON_BackPays($BackPayID);
		
		$dt = PdoDataAccess::runquery("
			select DocID from ACC_DocItems where SourceType=" . DOCTYPE_INSTALLMENT_PAYMENT . " 
			AND SourceID=? AND SourceID2=?" , array($obj->RequestID, $obj->BackPayID), $pdo);
		if(count($dt) == 0)
			return 0;
		return $dt[0][0];
	}
}

class LON_payments extends OperationClass
{
	const TableName = "LON_payments";
	const TableKey = "PayID";

	public $PayID;
	public $RequestID;
	public $PayType;
	public $PayDate;
	public $PayAmount;
	public $DocID;
	
	function __construct($PayID = "") {
		
		$this->DT_PayDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($PayID != "")
			parent::FillObject ($this, "select *
				from LON_payments  where payID=?", array($PayID));
	}
	
	static function Get($where = '', $whereParams = array()) {
		
		return parent::runquery_fetchMode("select p.*,c.LocalNo,c.DocStatus 
			from LON_payments p
			left join ACC_docs c using(DocID) where 1=1 " . $where, $whereParams);
	}
	
	function CheckPartAmount(){
		
		$dt = parent::runquery("select ifnull(sum(PayAmount),0) from LON_payments 
			where RequestID=? AND PayID<>?", array($this->RequestID, $this->PayID));
		
		$PartObj = LON_ReqParts::GetValidPartObj($this->RequestID);
		
		if($dt[0][0]*1 + $this->PayAmount*1 > $PartObj->PartAmount*1)
		{
			ExceptionHandler::PushException("مبالغ وارد شده از سقف مبلغ وام تجاوز می کند");
			return false;
		}
		
		return true;
	}
	
	function Add($pdo = null) {
		
		if(!$this->CheckPartAmount())
			return false;
		
		return parent::Add($pdo);
	}
	
	function Edit($pdo = null) {
		
		if(!$this->CheckPartAmount())
			return false;
		
		return parent::Edit($pdo);
	}
}

class LON_messages extends OperationClass {

	const TableName = "LON_messages";
	const TableKey = "MessageID";
	
	public $MessageID;
	public $RequestID;
	public $RegPersonID;
	public $CreateDate;
	public $details;
	public $MsgStatus;
	public $DoneDate;
	public $DoneDesc;
	
	static function Get($where = '', $whereParams = array(), $order = "order by CreateDate desc") {
		
		return PdoDataAccess::runquery_fetchMode("
		select	m.* , r.BorrowerDesc,
				concat_ws(' ',p.fname,p.lname,p.CompanyName) RegPersonName,
				concat_ws(' ',p2.fname,p2.lname,p2.CompanyName) LoanFullname
				
		from LON_messages m
		join BSC_persons p on(RegPersonID = PersonID)
		join LON_requests r using(RequestID)
		left join BSC_persons p2 on(p2.PersonID=r.LoanPersonID)
		
		where 1=1 " . $where . " " . $order, $whereParams);	
	}
}

class LON_events extends OperationClass {

    const TableName = "LON_events";
    const TableKey = "EventID";

    public $EventID;
	public $RequestID;
	public $RegPersonID;
    public $EventTitle;
    public $EventDate;
	public $LetterID;
	
    function __construct($id = ""){
        
		$this->DT_EventDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
        parent::__construct($id);
    }

	static function Get($where = '', $whereParams = array()) {
		
		return PdoDataAccess::runquery_fetchMode("
			select e.*, concat_ws(' ',CompanyName, fname,lname) RegFullname
			from LON_events e left join BSC_persons on(PersonID=RegPersonID)
			where 1=1 " . $where, $whereParams);
	}
	
}

class LON_costs extends OperationClass
{
	const TableName = "LON_costs";
	const TableKey = "CostID";
	
	public $CostID;
	public $RequestID;
	public $CostDesc;
	public $CostAmount;
	public $IsPartDiff;
	public $PartID;
	
	static function Get($where = '', $whereParams = array()) {
		
		return PdoDataAccess::runquery_fetchMode("
			select c.*,d.LocalNo from LON_costs c
			left join ACC_DocItems di on(c.CostID=di.SourceID2 AND di.SourceType=17)
			left join ACC_docs d using(DocID)
			where 1=1 " . $where . " group by CostID", $whereParams);
	}
	
	function GetAccDoc(){
		
		$dt = PdoDataAccess::runquery("select d.* 
			from ACC_DocItems di join ACC_docs d using(DocID) 
			where SourceID=? AND SourceID2=? AND SourceType=17
			group by DocID", array($this->RequestID, $this->CostID));
		
		return count($dt) > 0 ? $dt[0] : false;
	}

}
?>
