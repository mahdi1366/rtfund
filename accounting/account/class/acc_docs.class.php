<?php

//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.01
//-----------------------------

class manage_acc_docs extends PdoDataAccess {

	public $cycleID;
	public $docID;
	public $docDate;
	public $regDate;
	public $docStatus;
	public $description;
	public $ref_docID;
	
	public $docType;
	//public $storeDocID;
	public $DocTypeInfo;
	public $regPersonID;
	public $CashPay;
	public $detail;
	
	public $_oldDocID;
		
	function __construct($DocID = "") {
		$this->DT_docDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_regDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($DocID != "")
			parent::FillObject ($this, "select * from acc_docs where docID=?", array($DocID));
	}

	static function GetAll($where = "", $whereParam = array()) {
		
		$query = "select sd.*, concat(fname,' ',lname) as regPerson,group_concat(sd2.docID) atf
			from acc_docs sd
			join persons p on(regPersonID=personID)
			left join acc_docs sd2 on(sd2.ref_docID=sd.docID)
		";
		
		$st = preg_split('/order by/', $where);
		$query .= ($where != "") ? " where " . $st[0] : "";
		$query .= " group by sd.docID";
		$query .= (count($st) > 1 && $st[1] != "") ? " order by " . $st[1] : "";
		
		return parent::runquery($query, $whereParam);
	}

	function Add($pdo = null) {
		$pdo2 = $pdo == null ? parent::getPdoObject() : $pdo;
		if ($pdo == null)
			$pdo2->beginTransaction();

		if($this->docID == "")
			$this->docID = parent::GetLastID("acc_docs", "docID", "", array(), $pdo2) + 1;
		
		if (parent::insert("acc_docs", $this, $pdo2) === false) {
			if ($pdo == null)
				$pdo2->rollBack();
			return false;
		}

		parent::AUDIT("acc_docs", "ایجاد سند حسابداری", $this->docID, 0, 0, $pdo2);

		if ($pdo == null)
			$pdo2->commit();
		return true;
	}

	function Edit($confirm = false, $regResid = false, $pdo = null) {
		
		$whereParams = array();
		$whereParams[":did"] = $this->_oldDocID;

		if (parent::update("acc_docs", $this, " docID=:did", $whereParams, $pdo) === false)
			return false;

		if ($confirm)
			parent::AUDIT("acc_docs", "تایید سند", $this->docID, 0, 0, $pdo);
		else
			parent::AUDIT("acc_docs", "ویرایش سند حسابداری", $this->docID, $this->_oldDocID, 0, $pdo);
		return true;
	}

	static function Remove($docID) {
		$temp = parent::runquery("select * from acc_docs where docID=?", array($docID));
		if (count($temp) == 0)
			return false;

		if ($temp[0]["docStatus"] == "RAW") {
			
			$pdo = parent::getPdoObject();
			$pdo->beginTransaction();
			
			$result = parent::runquery("update store_docs set accDocID=null where accDocID=?", array($docID), $pdo);
			if ($result === false)
			{
				$pdo->rollBack();
				return false;
			}
			
			$result = parent::runquery("update acc_docs set ref_docID=null where ref_docID=?", array($docID), $pdo);
			if ($result === false)
			{
				$pdo->rollBack();
				return false;
			}
						
			$result = parent::delete("acc_checks", "docID=?", array($docID), $pdo);
			if ($result === false)
			{
				$pdo->rollBack();
				return false;
			}
			
			$result = parent::delete("acc_doc_items", "docID=?", array($docID), $pdo);
			if ($result === false)
			{
				$pdo->rollBack();
				return false;
			}

			$result = parent::delete("acc_docs", "docID=?", array($docID), $pdo);
			if ($result === false)
			{
				$pdo->rollBack();
				return false;
			}

			PdoDataAccess::AUDIT("acc_docs", "حذف سند حسابداری", $docID, 0, 0, $pdo);
			
			$pdo->commit();
			return true;
		}

		$result = parent::runquery("update acc_docs set docStatus='DELETED'
				where docID=:did ", array(":did" => $docID));

		if ($result === false)
			return false;

		PdoDataAccess::AUDIT("acc_docs", "حذف سند حسابداری", $docID);
		return true;
	}

}

class manage_acc_doc_items extends PdoDataAccess {

	public $docID;
	public $rowID;
	public $kolID;
	public $moinID;
	public $tafsiliID;
	public $tafsili2ID;
	public $bdAmount;
	public $bsAmount;
	public $details;

	public $locked;
	
	function __construct() {
	}

	static function GetAll($where = "", $whereParam = array()) {
		$query = "select si.*,kolTitle,moinTitle,t.tafsiliTitle,t2.tafsiliTitle as tafsiliTitle2
		from acc_doc_items si
			left join acc_kols k using(kolID)
			left join acc_moins m on(m.kolID=si.kolID AND m.moinID=si.moinID)
			left join acc_tafsilis t on(t.tafsiliID=si.tafsiliID)
			left join acc_tafsilis t2 on(t2.tafsiliID=si.tafsili2ID)
			";
		$query .= ($where != "") ? " where " . $where : "";
		return parent::runquery_fetchMode($query, $whereParam);
	}

	function Add($pdo = null) {
		$this->rowID = (int) parent::GetLastID("acc_doc_items", "rowID", "docID=?", array($this->docID),$pdo) + 1;

		if (!parent::insert("acc_doc_items", $this, $pdo))
			return false;
		parent::AUDIT("acc_doc_items", "ایجاد ردیف سند حسابداری", $this->docID, $this->rowID, "", $pdo);
		return true;
	}

	function Edit() {
		if (!parent::update("acc_doc_items", $this, "docID=:c AND rowID=:rid", 
				array(":c" => $this->docID, ":rid" => $this->rowID)))
			return false;

		parent::AUDIT("acc_doc_items", "ویرایش ردیف سند حسابداری", $this->docID, $this->rowID);
		return true;
	}

	static function Remove($docID, $rowID) {
		$result = parent::delete("acc_doc_items", "docID=:c AND rowID=:rid", 
				array(":c" => $docID, ":rid" => $rowID));

		if ($result === false)
			return false;

		PdoDataAccess::AUDIT("acc_doc_items", "حذف ردیف از سند حسابداری", $docID, $rowID);
		return true;
	}

}

class manage_acc_checks extends PdoDataAccess
{
    public $checkID;
    public $docID;
	public $checkNo;
	public $accountID;
	public $checkDate;
	public $amount;
	public $reciever;
	public $checkStatus;
	
	public $tafsiliID;
	public $description;

    function __construct()
    {
		$this->DT_checkDate = DataMember::CreateDMA(DataMember::DT_DATE);
    }

    static function GetAll($where = "",$whereParam = array())
    {
	    $query = "select c.*,a.*,b.title as checkTitle,t.tafsiliTitle,bb.PrintPage1,bb.PrintPage2,bb.bankTitle
					from acc_checks c
					join acc_accounts a using(accountID)
					join banks bb using(bankID)
					join basic_info b on(b.typeID=3 AND b.infoID=checkStatus)
					left join acc_tafsilis t using(tafsiliID)
					";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery($query, $whereParam);
    }

    function Add()
    {
	    if( parent::insert("acc_checks", $this) === false )
		    return false;

	    $this->checkID = parent::InsertID();

	    parent::AUDIT("acc_checks","ایجاد چک", $this->checkID);
	    return true;
    }

    function Edit()
    {
	    $whereParams = array();
	    $whereParams[":kid"] = $this->checkID;

	    if( parent::update("acc_checks",$this," checkID=:kid", $whereParams) === false )
		    return false;

	    parent::AUDIT("acc_checks","ویرایش اطلاعات چک", $this->checkID);
	    return true;
    }

    static function Remove($checkID)
    {
	    $result = parent::delete("acc_checks", "checkID=:kid ",
		    array(":kid" => $checkID));

	    if($result === false)
		    return false;

	    PdoDataAccess::AUDIT("acc_checks","حذف چک", $checkID);

	    return true;
    }
}

?>