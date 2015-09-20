<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

class manage_moins extends PdoDataAccess
{
    public $kolID;
	public $moinID;
    public $moinTitle;

    function __construct($kolID = "", $moinID = "")
    {
		if($moinID != "")
			parent::FillObject($this, "select * from acc_moins where kolID=? AND moinID=?", array($kolID, $moinID));
    }

    static function GetAll($where = "",$whereParam = array())
    {
	    $query = "select * from acc_moins
			join acc_kols using(kolID)";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery($query, $whereParam);
    }

    function Add()
    {
		if($this->moinID == "")
			unset($this->moinID);
		
	    if( parent::insert("acc_moins", $this) === false )
		    return false;

	    $this->moinID = parent::InsertID();

	    parent::AUDIT("acc_moins","ایجاد حساب معین", $this->moinID);
	    return true;
    }

    function Edit($oldID)
    {
	    $whereParams = array();
	    $whereParams[":kid"] = $this->kolID;
		$whereParams[":mid"] = $oldID;

	    if( parent::update("acc_moins",$this,"kolID=:kid AND moinID=:mid", $whereParams) === false )
		    return false;

	    parent::AUDIT("acc_moins","ویرایش اطلاعات حساب معین", $this->kolID, $oldID);
	    return true;
    }

    static function Remove($kolID, $moinID)
    {
	    $result = parent::delete("acc_moins", "kolID=:kid AND moinID=:mid ",
		    array(":kid" => $kolID, ":mid" => $moinID));

	    if($result === false)
		    return false;

	    PdoDataAccess::AUDIT("acc_moins","حذف حساب معین", $kolID, $moinID);

	    return true;
    }
}

?>