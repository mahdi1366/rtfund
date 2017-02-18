<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.07
//-----------------------------

class ACC_IncomeCheques extends OperationClass{
	
	const TableName = "ACC_IncomeCheques";
	const TableKey = "IncomeChequeID";
	
	public $IncomeChequeID;
	public $CostID;
	public $TafsiliType;
	public $TafsiliID;
	public $TafsiliType2;
	public $TafsiliID2;
	public $ChequeNo;
	public $ChequeDate;
	public $ChequeAmount;
	public $ChequeBank;
	public $ChequeBranch;
	public $ChequeStatus;
	public $description;
	public $EqualizationID;
	public $PayedDate;
	
	function __construct($id = '') {
		
		$this->DT_ChequeDate = DataMember::CreateDMA(DataMember::DT_DATE);		
		$this->DT_PayedDate = DataMember::CreateDMA(DataMember::DT_DATE);		
		parent::__construct($id);
	}
	
	static function Get($where = "", $param = array()){
		
		$query = "
			SELECT o.*,
				cc.CostCode,
				concat_ws('-', b1.blockDesc, b2.blockDesc, b3.blockDesc, b4.blockDesc) CostDesc,
				b.BankDesc, 
				bi2.InfoDesc ChequeStatusDesc,
				d.LocalNo,
				d.DocStatus
			
			FROM ACC_IncomeCheques o

			join ACC_CostCodes cc using(CostID)
			left join ACC_blocks b1 on(cc.level1=b1.BlockID)
			left join ACC_blocks b2 on(cc.level2=b2.BlockID)
			left join ACC_blocks b3 on(cc.level3=b3.BlockID)
			left join ACC_blocks b4 on(cc.level4=b4.BlockID)

			left join ACC_banks b on(ChequeBank=BankID)
			left join BaseInfo bi2 on(bi2.TypeID=16 AND bi2.InfoID=o.ChequeStatus)
			
			left join ACC_DocItems di on(SourceID=IncomeChequeID AND SourceType=" . DOCTYPE_INCOMERCHEQUE . ")
			left join ACC_docs d on(di.DocID=d.DocID)
			
			where 1=1 " . $where;
		
		return parent::runquery_fetchMode($query, $param);
	}
	
	static function AddToHistory($IncomeChequeID, $status, $pdo = null){
		
		PdoDataAccess::runquery("insert into ACC_ChequeHistory(IncomeChequeID,StatusID,PersonID,ATS)
			values(?,?,?,now())", array(
				$IncomeChequeID,
				$status,
				$_SESSION["USER"]["PersonID"]
			),$pdo);
	}

	function Add($pdo = null) {
		
		$dt = self::Get(" AND ChequeNo=?", array($this->ChequeNo));
		if($dt->rowCount() > 0)
		{
			ExceptionHandler::PushException("چک دیگری با این شماره قبلا ثبت شده است");
			return false;
		}
		
		return parent::Add($pdo);
	}
	
	function GetBackPays($pdo = null){
		
		return PdoDataAccess::runquery("
			select * from LON_BackPays 
				join LON_requests using(RequestID)
				left join ACC_tafsilis t2 on(t2.TafsiliType=".TAFTYPE_PERSONS." AND t2.ObjectID=LoanPersonID)
			where IncomeChequeID=?", 
				array($this->IncomeChequeID), $pdo);
	}
}

?>