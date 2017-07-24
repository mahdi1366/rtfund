<?php

//-----------------------------
//	Programmer	: Jafarkhani
//	Date		: 94.08
//-----------------------------

class WFM_forms extends OperationClass {

    const TableName = "WFM_forms";
    const TableKey = "FormID";
	
	const TplItemSeperator = "#";

	public $FormID;
    public $FormTitle;
    public $FormContent;
    public $IsActive;
	public $FlowID;
	public $IsStaff;
	public $IsCustomer;
	public $IsShareholder;
	public $IsSupporter;
	public $IsExpert;
	public $IsAgent;

    public function Remove($pdo = null){
        $res = parent::runquery("select count(*) from WFM_requests where FormID = ? limit 1",
				array($this->FormID),$pdo);
        if ($res[0][0] > 0)
		{
			parent::runquery("update " . static::TableName . " 
				set IsActive='NO' where FormID=?", array($this->FormID));
			return ExceptionHandler::GetExceptionCount() == 0;
        }
		
		parent::runquery("delete from WFM_FormItems where FormID=?", array($this->FormID), $pdo);
		
        return parent::Remove($pdo);
    }    
    
	static function CorrectFormContentItems($content) {
		
        $contentsArr = explode(self::TplItemSeperator, $content);
        $CorrectContent = '';
        if (substr($content, 0, 3) === self::TplItemSeperator) {
            $contentsArr = array_merge(array(''), $contentsArr);
        }

        for ($i = 0; $i < count($contentsArr); $i++) {
            $ArrCell = $contentsArr[$i];
            if ($i % 2 == 0) {
                $CorrectContent .= $ArrCell;
            } else {
                $arr = explode('--', $ArrCell);
                $CorrectContent .= self::TplItemSeperator . $arr[0] . self::TplItemSeperator;
            }
        }
        return $CorrectContent;
    }
}

class WFM_FormItems extends OperationClass {
    
    const TableName = "WFM_FormItems";
    const TableKey = "FormItemID";

    public $FormItemID; 
	public $FormID;
    public $ItemName;
    public $ItemType;
	public $ComboValues;
    
    public function Remove($pdo = null){
        $res = parent::runquery("select count(*) from WFM_FormItems where FormItemID = ? limit 1",
				array($this->FormItemID),$pdo);
        if ($res[0][0] > 0){
			parent::PushException("UsedTemplateItem");
			return false;
        }
        return parent::Remove($pdo);
    }    
}

class WFM_FormPersons extends OperationClass {

	const TableName = "WFM_FormPersons";
	const TableKey = "RowID"; 
	
	public $RowID;
	public $FormID;
	public $PersonID;
	
	static function Get($where = '', $whereParams = array()) {
		
		return parent::runquery_fetchMode("select fp.*, concat_ws(' ',fname,lname,CompanyName) fullname 
			from WFM_FormPersons fp join BSC_persons p using(PersonID)
			where 1=1 " . $where, $whereParams);
	}
}


class WFM_requests extends OperationClass {

    const TableName = "WFM_requests";
    const TableKey = "RequestID";

    public $RequestID;
    public $FormID;
    public $PersonID;
    public $RegDate;
	public $ReqContent;

	public $_FormTitle;
	public $_PersonName;
    
    public function __construct($id = ""){
        
        if ($id != ''){
            parent::FillObject($this, "
					select r.* ,  f.FormTitle as _FormTitle,
						concat_ws(' ',fname,lname,CompanyName) _PersonName
                    from WFM_requests r
                    left join WFM_forms f using(FormID) 
					left join BSC_persons using(PersonID)
					where r.RequestID = :id", array(":id" => $id));
        }
    }

    public static function Get($where = '', $whereParams = array(), $order = "") {
		
        return parent::runquery_fetchMode("
			select r.RequestID,
				r.FormID,
				f.FlowID,
				r.PersonID,
				r.RegDate,
				f.FormTitle,
				concat_ws(' ',fname,lname,CompanyName) fullname
			
			from WFM_requests r
			join WFM_forms f using(FormID) 
			join BSC_persons using(PersonID)
			
			where 1=1 " . $where . $order, $whereParams);
    }
}

class WFM_RequestItems extends OperationClass {

    const TableName = "WFM_RequestItems";
    const TableKey = "ReqItemID";

    public $ReqItemID;
    public $RequestID;
    public $FormItemID;
    public $ItemValue;

	static public function Get($where="", $param=array()){
		
		$query = "select * from WFM_RequestItems
			join WFM_requests r using(RequestID)
			join WFM_FormItems using(FormItemID)
			where 1=1 " . $where;
		return PdoDataAccess::runquery_fetchMode($query, $param);
	}
	
    public static function RemoveAll($RequestID, $pdo = null) {
		
        return parent::delete(static::TableName, "RequestID=:RequestID", 
				array(":RequestID" => $RequestID), $pdo);
	}
}
?>
