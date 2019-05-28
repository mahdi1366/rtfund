<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 98.03
//-----------------------------

class ACC_budgets extends OperationClass {

	const TableName = "ACC_budgets";
	const TableKey = "BudgetID";     
	
	public $BudgetID;
	public $BudgetDesc;
	public $IsActive;
	
}

class ACC_BudgetCostCodes extends OperationClass {

	const TableName = "ACC_BudgetCostCodes";
	const TableKey = "RowID";     
	
	public $RowID;
	public $BudgetID;
	public $CostID;
	
	static function Get($where = '', $whereParams = array(), $pdo = null) {
		
		return PdoDataAccess::runquery_fetchMode("
			select b.*, cc.CostCode,concat_ws('-',b1.blockDesc,b2.BlockDesc,b3.BlockDesc,b4.BlockDesc) CostDesc
				from ACC_BudgetCostCodes b
				join ACC_CostCodes cc using(CostID)
				left join ACC_blocks b1 on(cc.level1=b1.blockID)
				left join ACC_blocks b2 on(cc.level2=b2.blockID)
				left join ACC_blocks b3 on(cc.level3=b3.blockID)
				left join ACC_blocks b4 on(cc.level4=b4.blockID)
				where 1=1 " . $where, $whereParams, $pdo);
	}
}

class ACC_BudgetAlloc extends OperationClass {

	const TableName = "ACC_BudgetAlloc";
	const TableKey = "AllocID";     
	
	public $AllocID;
	public $BudgetID;
	public $AllocDate;
	public $AllocAmount;
	public $details;
	
	function __construct($id = '') {
		
		$this->DT_AllocDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		return parent::__construct($id);
	}
	
	static function Get($where = '', $whereParams = array(), $pdo = null) {
		
		return PdoDataAccess::runquery_fetchMode("
			select a.*, b.BudgetDesc
				from ACC_BudgetAlloc a
				join ACC_budgets b using(BudgetID)
				where 1=1 " . $where, $whereParams, $pdo);
	}
}

?>