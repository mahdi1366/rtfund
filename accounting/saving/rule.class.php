<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 95.03
//---------------------------
 
class ACC_SavingRules extends OperationClass {

	const TableName = "ACC_SavingRules";
	const TableKey = "RuleID";
	
	public $RuleID;
	public $RuleDesc;
	public $WagePercent;
	public $FromDate;
	public $ToDate;
	public $MinAmount;
	public $MaxAmount;
	public $details;

	function __construct($id = '') {
		
		$this->DT_FromDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_ToDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		parent::__construct($id);
	}
}

class ACC_RulePeriods extends OperationClass {

	const TableName = "ACC_RulePeriods";
	const TableKey = "RowID";
	
	public $RowID;
	public $RuleID;
	public $months;
	public $InstallmentCount;
}
?>
