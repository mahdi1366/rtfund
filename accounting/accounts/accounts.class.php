<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

class manage_accounts extends PdoDataAccess
{
    public $accountID;
    public $bankID;
	public $branchTitle;
	public $accountNo;
	public $accountTitle;

	public $StartNo;
	public $EndNo;
	
	public $StartNo2;
	public $EndNo2;
	
    function __construct()
    {

    }

    static function GetAll($where = "",$whereParam = array())
    {
	    $query = "select * from acc_accounts ";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery($query, $whereParam);
    }

    function Add()
    {
	    if( parent::insert("acc_accounts", $this) === false )
		    return false;

	    $this->accountID = parent::InsertID();

	    parent::AUDIT("acc_accounts","ایجاد حساب بانک", $this->accountID);
	    return true;
    }

    function Edit()
    {
	    $whereParams = array();
	    $whereParams[":kid"] = $this->accountID;

	    if( parent::update("acc_accounts",$this," accountID=:kid", $whereParams) === false )
		    return false;

	    parent::AUDIT("acc_accounts","ویرایش اطلاعات حساب بانک", $this->accountID);
	    return true;
    }

    static function Remove($accountID)
    {
	    $result = parent::delete("acc_accounts", "accountID=:kid ",
		    array(":kid" => $accountID));

	    if($result === false)
		    return false;

	    PdoDataAccess::AUDIT("acc_accounts","حذف حساب بانک", $accountID);

	    return true;
    }
}

?>