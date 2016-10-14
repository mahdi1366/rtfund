<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.08
//-----------------------------

class VOT_forms extends OperationClass{

	const TableName = "VOT_forms";
	const TableKey = "FormID"; 
	
	public $FormID;
	public $FormTitle;
	public $StartDate;
	public $EndDate;
	public $IsStaff;
	public $IsCustomer;
	public $IsShareholder;
	public $IsSupporter;
	public $IsExpert;
	public $IsAgent;


	function __construct($FormID = '') {
		
		$this->DT_StartDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_EndDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		parent::__construct($FormID);
	}

}

class VOT_FormItems extends OperationClass {

	const TableName = "VOT_FormItems";
	const TableKey = "ItemID"; 
	
	public $ItemID;
	public $FormID;
	public $ItemType;
	public $ItemTitle;
	public $ItemValues;
	public $ordering;

}

?>