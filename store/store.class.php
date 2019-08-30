<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 98.03
//-----------------------------

class STO_goods extends OperationClass {

	const TableName = "ACC_budgets";
	const TableKey = "BudgetID";     
	
	public $BudgetID;
	public $ParentID;
	public $BudgetDesc;
	public $IsActive;
}

?>