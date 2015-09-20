<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

class manage_banks extends PdoDataAccess
{
    public $bankID;
    public $bankTitle;

    function __construct()
    {

    }

    static function GetAll($where = "",$whereParam = array())
    {
	    $query = "select * from banks";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery($query, $whereParam);
    }

    function Add()
    {
	    if( parent::insert("banks", $this) === false )
		    return false;

	    $this->bankID = parent::InsertID();

	    parent::AUDIT("banks","ایجاد بانک", $this->bankID);
	    return true;
    }

    function Edit()
    {
	    $whereParams = array();
	    $whereParams[":kid"] = $this->bankID;

	    if( parent::update("banks",$this," bankID=:kid", $whereParams) === false )
		    return false;

	    parent::AUDIT("banks","ویرایش اطلاعات بانک", $this->bankID);
	    return true;
    }

    static function Remove($bankID)
    {
	    $result = parent::delete("banks", "bankID=:kid ",
		    array(":kid" => $bankID));

	    if($result === false)
		    return false;

	    PdoDataAccess::AUDIT("banks","حذف بانک", $bankID);

	    return true;
    }
}

?>