<?php
//---------------------------
// programmer:	khoshroo
// create Date: 96.03
//---------------------------

class ATN_ExtraSummary extends OperationClass{
	const TableName = "ATN_ExtraSummary";
	const TableKey = "SummaryID";
	
	public $SummaryID;
	public $PersonID;
	public $SummaryYear;
	public $SummaryMonth;
	public $RealAmount;
	public $LegalAmount;
	public $AllowedAmount;
	public $FinalAmount;
	public $StatusCode;
	
	static function Get($where = '', $whereParams = array()) {
		
		$query = "select concat_ws(' ',fname,lname,CompanyName) fullname,s.*
			from ATN_ExtraSummary s
			join BSC_persons using(PersonID)
			where 1=1 " . $where;
		
		return parent::runquery_fetchMode($query, $whereParams);		
	}
	
	static function RemoveAll($SummaryYear,$SummaryMonth, $pdo = null){
		
		return PdoDataAccess::delete(self::TableName, " SummaryYear=? and SummaryMonth=?", 
			array($SummaryYear, $SummaryMonth), $pdo);
	}
}


?>
