<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 95.06
//---------------------------

class NTC_operations extends OperationClass
{
	const TableName = "NTC_operations";
	const TableKey = "OperationID";
	
	public $OperationID;
	public $title;
	public $OperationDate;
	public $SendType;
	public $context;
	public $GroupLetter;
}

class NTC_persons extends OperationClass
{
	const TableName = "NTC_persons";
	const TableKey = "RowID";
	
	public $RowID;
	public $OperationID;
	public $PersonID;
	public $LetterID;
	public $context;
	public $IsSuccess;
	public $ErrorMsg;

	static function Get($where = '', $whereParams = array(), $pdo = null) {
		return PdoDataAccess::runquery_fetchMode("
			select n.*,p.mobile,p.email from NTC_persons n 
			join BSC_persons p using(PersonID) where 1=1 " . $where, $whereParams, $pdo);
	}
}

class NTC_templates extends OperationClass
{
	const TableName = "NTC_templates";
	const TableKey = "TemplateID";
	
	public $TemplateID;
	public $TemplateTitle;
	public $context;
	public $SendType;
}
?>
