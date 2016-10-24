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
	public $TafsiliID;
	public $ChequeNo;
	public $ChequeDate;
	public $ChequeAmount;
	public $ChequeBank;
	public $ChequeBranch;
	public $ChequeStatus;
	
	static function Get($where = "", $param = array()){
		
		$query = "
			SELECT o.*,
				cc.CostCode,
				concat_ws('-', b1.blockDesc, b2.blockDesc, b3.blockDesc) CostDesc,
				b.BankDesc, 
				bi.InfoDesc PayTypeDesc, 
				bi2.InfoDesc ChequeStatusDesc,
				d.LocalNo,
				d.DocStatus
			
			FROM ACC_OuterCheques o

			join ACC_CostCodes cc using(CostID)
			left join ACC_blocks b1 on(cc.level1=b1.BlockID)
			left join ACC_blocks b2 on(cc.level2=b2.BlockID)
			left join ACC_blocks b3 on(cc.level3=b3.BlockID)

			left join ACC_banks b on(ChequeBank=BankID)
			left join BaseInfo bi2 on(bi2.TypeID=16 AND bi2.InfoID=p.ChequeStatus)
			
			left join ACC_DocItems di on(SourceID=OuterChequeID AND SourceType=" . DOCTYPE_OUTERCHEQUE . ")
			left join ACC_docs d on(di.DocID=d.DocID)
		";
		
		return parent::runquery_fetchMode($query, $param);
	}
}

?>