<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.12
//-----------------------------

class ACC_DepositePercents extends OperationClass {

	const TableName = "ACC_DepositePercents";
	const TableKey = "RowID";
	
	public $RowID;
	public $TafsiliID;
	public $FromDate;
	public $ToDate;
	public $percent;
	
	function __construct($id = '') {
		
		$this->DT_FromDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_ToDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		parent::__construct($id);
	}
	
	static function Get($where = "", $param = array()){
		
		return PdoDataAccess::runquery_fetchMode("
			select * from ACC_DepositePercents 
				join ACC_tafsilis using(TafsiliID)
			where 1=1 " . $where, $param);
	}

}
?>