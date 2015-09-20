<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

class manage_tafsilis extends PdoDataAccess
{
    public $tafsiliID;
    public $tafsiliTitle;
	public $description;
	public $TafsiliType;
	
    function __construct($tafsiliID = "")
    {
		if($tafsiliID != "")
			parent::FillObject($this, "select * from acc_tafsilis where tafsiliID=?", 
					array($tafsiliID));
    }

    static function GetAll($where = "",$whereParam = array())
    {
	    $query = "select * from acc_tafsilis ";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery($query, $whereParam);
    }

    function Add()
    {
		if($this->tafsiliID == "")
			unset($this->tafsiliID);
		
	    if( parent::insert("acc_tafsilis", $this) === false )
		    return false;

	    $this->tafsiliID = parent::InsertID();

	    parent::AUDIT("acc_tafsilis","ایجاد حساب تفصیلی", $this->tafsiliID);
	    return true;
    }

    function Edit($oldID)
    {
	    $whereParams = array();
	    $whereParams[":kid"] = $oldID;
		
	    if( parent::update("acc_tafsilis",$this,"tafsiliID=:kid", $whereParams) === false )
		    return false;

	    parent::AUDIT("acc_tafsilis","ویرایش اطلاعات حساب تفصیلی", $oldID, $this->tafsiliID);
	    return true;
    }

    static function Remove($tafsiliID)
    {
	    $result = parent::delete("acc_tafsilis", "tafsiliID=:kid ",
		    array(":kid" => $tafsiliID));

	    if($result === false)
		    return false;

	    PdoDataAccess::AUDIT("acc_tafsilis","حذف حساب تفصیلی", $tafsiliID);

	    return true;
    }
}

?>