<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.07
//-----------------------------

class ACC_OuterCheques extends OperationClass{
	
	const TableName = "ACC_OuterCheques";
	const TableKey = "OuterChequeID";
	
	public $OuterChequeID;
	public $CostID;
	public $TafsiliType;
	public $TafsiliID;
	public $ChequeNo;
	public $ChequeDate;
	public $ChequeAmount;
	public $ChequeBank;
	public $ChequeBranch;
	public $ChequeStatus;
	
	function __construct($id = '') {
		
		$this->DT_ChequeDate = DataMember::CreateDMA(DataMember::DT_DATE);		
		parent::__construct($id);
	}
	
	static function Get($where = "", $param = array()){
		
		$query = "
			SELECT o.*,
				cc.CostCode,
				concat_ws('-', b1.blockDesc, b2.blockDesc, b3.blockDesc) CostDesc,
				b.BankDesc, 
				bi2.InfoDesc ChequeStatusDesc,
				d.LocalNo,
				d.DocStatus
			
			FROM ACC_OuterCheques o

			join ACC_CostCodes cc using(CostID)
			left join ACC_blocks b1 on(cc.level1=b1.BlockID)
			left join ACC_blocks b2 on(cc.level2=b2.BlockID)
			left join ACC_blocks b3 on(cc.level3=b3.BlockID)

			left join ACC_banks b on(ChequeBank=BankID)
			left join BaseInfo bi2 on(bi2.TypeID=16 AND bi2.InfoID=o.ChequeStatus)
			
			left join ACC_DocItems di on(SourceID=OuterChequeID AND SourceType=" . DOCTYPE_OUTERCHEQUE . ")
			left join ACC_docs d on(di.DocID=d.DocID)
			
			where 1=1 " . $where;
		
		return parent::runquery_fetchMode($query, $param);
	}
	
	static function AddToHistory($BackPayID, $OuterChequeID, $status, $pdo = null){
		
		PdoDataAccess::runquery("insert into ACC_ChequeHistory(BackPayID,OuterChequeID,StatusID,PersonID,ATS)
			values(?,?,?,?,now())", array(
				$BackPayID,
				$OuterChequeID,
				$status,
				$_SESSION["USER"]["PersonID"]
			),$pdo);
	}

	function Add($pdo = null) {
		
		$dt = self::Get(" AND ChequeNo=? AND ChequeDate=?", array($this->ChequeNo, DateModules::shamsi_to_miladi($this->ChequeDate)));
		if($dt->rowCount() > 0)
		{
			ExceptionHandler::PushException("چک دیگری با این شماره و تاریخ قبلا ثبت شده است");
			return false;
		}
		
		parent::Add($pdo);
	}
	
}

?>