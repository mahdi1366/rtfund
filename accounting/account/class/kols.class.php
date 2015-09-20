<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

class manage_kols extends PdoDataAccess
{
    public $kolID;
    public $kolTitle;

    function __construct($kolID = "")
    {
		if($kolID != "")
			parent::FillObject($this, "select * from acc_kols where kolID=?", array($kolID));
    }

    static function GetAll($where = "",$whereParam = array())
    {
	    $query = "select * from acc_kols";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery($query, $whereParam);
    }

    function Add()
    {
		if($this->kolID == "")
			unset($this->kolID);
		
	    if( parent::insert("acc_kols", $this) === false )
		    return false;

	    $this->kolID = parent::InsertID();

	    parent::AUDIT("acc_kols","ایجاد حساب کل", $this->kolID);
	    return true;
    }

    function Edit($oldID)
    {
	    $whereParams = array();
	    $whereParams[":kid"] = $oldID;

	    if( parent::update("acc_kols",$this," kolID=:kid", $whereParams) === false )
		    return false;

	    parent::AUDIT("acc_kols","ویرایش اطلاعات حساب کل", $oldID);
	    return true;
    }

    static function Remove($kolID)
    {
	    $result = parent::delete("acc_kols", "kolID=:kid ",
		    array(":kid" => $kolID));

	    if($result === false)
		    return false;

	    PdoDataAccess::AUDIT("acc_kols","حذف حساب کل", $kolID);

	    return true;
    }
}

?>