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

	function Remove($pdo = null) {
		
		$dt = PdoDataAccess::runquery("select FormID from VOT_FilledForms"
			. " where FormID=?", array($this->FormID), $pdo);
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("این فرم تکمیل شده است و قادر به حذف آن نمی باشید");
			return false;
		}		
		
		PdoDataAccess::runquery("delete from VOT_FormGroups where FormID=?",
			array($this->FormID), $pdo);
		PdoDataAccess::runquery("delete from VOT_FormItems where FormID=?",
			array($this->FormID), $pdo);
		
		return parent::Remove($pdo);
	}
}

class VOT_FormGroups extends OperationClass {

	const TableName = "VOT_FormGroups";
	const TableKey = "GroupID"; 
	
	public $GroupID;
	public $FormID;
	public $GroupDesc;
	public $GroupWeight;
	public $ordering;
	
	function Remove($pdo = null) {
		
		$dt = PdoDataAccess::runquery("select FormID from VOT_FilledItems join VOT_FormItems using(ItemID)"
			. " where GroupID=?", array($this->GroupID), $pdo);
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("این گروه تکمیل شده است و قادر به حذف آن نمی باشید");
			return false;
		}		
		
		PdoDataAccess::runquery("delete from VOT_FormItems where GroupID=?",
			array($this->GroupID), $pdo);
		
		return parent::Remove($pdo);
	}
}

class VOT_FormItems extends OperationClass {

	const TableName = "VOT_FormItems";
	const TableKey = "ItemID"; 
	
	public $ItemID;
	public $FormID;
	public $GroupID;
	public $ItemType;
	public $ItemTitle;
	public $ItemValues;
	public $ordering;
	public $weight;
	public $ValueWeights;
	
	static function Get($where = '', $whereParams = array(), $pdo = null) {
		
		return parent::runquery_fetchMode("select f.*,GroupDesc 
			from VOT_FormItems f join VOT_FormGroups g using(GroupID)
			where 1=1 " . $where, $whereParams);
	}
	
	function Remove($pdo = null) {
		
		$dt = PdoDataAccess::runquery("select FormID from VOT_FilledItems "
			. " where ItemID=?", array($this->ItemID), $pdo);
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("این آیتم تکمیل شده است و قادر به حذف آن نمی باشید");
			return false;
		}		
		
		return parent::Remove($pdo);
	}
}

class VOT_FormPersons extends OperationClass {

	const TableName = "VOT_FormPersons";
	const TableKey = "RowID"; 
	
	public $RowID;
	public $FormID;
	public $PersonID;
	
	static function Get($where = '', $whereParams = array(), $pdo = null) {
		
		return parent::runquery_fetchMode("
			select fp.*, concat_ws(' ',fname,lname,CompanyName) fullname 
			from VOT_FormPersons fp join BSC_persons p using(PersonID)
			where 1=1 " . $where, $whereParams);
	}
}

?>