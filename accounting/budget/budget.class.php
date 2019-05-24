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
	
}

?>