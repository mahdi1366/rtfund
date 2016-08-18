<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

class ACC_docs extends PdoDataAccess {

	public $DocID;
	public $CycleID;
	public $BranchID;
	public $LocalNo;
	public $DocDate;
	public $RegDate;
	public $DocStatus;
	public $DocType;
	public $SubjectID;
	public $description;
	public $regPersonID;
		
	function __construct($DocID = "",$pdo = null) {
		
		$this->DT_DocDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_RegDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($DocID != "")
			parent::FillObject ($this, "select * from ACC_docs where DocID=?", array($DocID), $pdo);
	}

	static function GetAll($where = "", $whereParam = array()) {
		
		$query = "select sd.*, concat(fname,' ',lname) as regPerson, 
				b.InfoDesc SubjectDesc,b2.InfoDesc DocTypeDesc
			
			from ACC_docs sd
			left join BaseInfo b on(b.TypeID=73 AND b.InfoID=SubjectID)
			left join BaseInfo b2 on(b2.TypeID=9 AND b2.InfoID=DocType)
			left join BSC_persons p on(regPersonID=PersonID)";
		
		$query .= ($where != "") ? " where " . $where : "";
		
		return parent::runquery_fetchMode($query, $whereParam);
	}

	function Trigger($pdo = null){
		
		if($this->LocalNo != "")
		{
			$dt = PdoDataAccess::runquery("select * from ACC_docs 
			where BranchID=? AND CycleID=? AND LocalNo=?", 
			array($this->BranchID, $this->CycleID, $this->LocalNo), $pdo);
		
			if(count($dt) > 0)
			{
				if(empty($this->DocID) || $this->DocID != $dt[0]["DocID"])
				{
					ExceptionHandler::PushException("شماره سند تکراری است");
					return false;
				}
			}
			//..................................................	
			$DocDate = $this->DocDate;
			if($DocDate == PDONOW)
				$DocDate = DateModules::Now();
			else
				$DocDate = DateModules::shamsi_to_miladi ($DocDate, "-");
			
			$dt = PdoDataAccess::runquery("select * from ACC_docs 
				where LocalNo>? AND BranchID=? AND CycleID=?
				order by LocalNo limit 1",
					array($this->LocalNo, $this->BranchID, $this->CycleID), $pdo);
			
			if(count($dt) > 0 && strcmp($DocDate,$dt[0]["DocDate"]) > 0)
			{
				ExceptionHandler::PushException("تاریخ سند باید از اسناد بعدی کوچکتر باشد.");
				return false;
			}
			//..................................................	
			$dt = PdoDataAccess::runquery("select * from ACC_docs where LocalNo<? order by LocalNo desc limit 1",
					array($this->LocalNo), $pdo);
			
			if(count($dt) > 0 && strcmp($DocDate,$dt[0]["DocDate"]) < 0)
			{
				if($this->DocDate == PDONOW)
					$this->DocDate = $dt[0]["DocDate"];
				else
				{
					ExceptionHandler::PushException("تاریخ سند باید از اسناد قبلی بزرگتر باشد.");
					return false;
				}
			}
		}
		
		return true;
		
	}
	
	function Add($pdo = null) {
		
		$pdo2 = $pdo == null ? parent::getPdoObject() : $pdo;
		if ($pdo == null)
			$pdo2->beginTransaction();

		if($this->LocalNo == "")
			$this->LocalNo = parent::GetLastID("ACC_docs", "LocalNo", 
				"CycleID=? AND BranchID=?", 
				array($this->CycleID, $this->BranchID), $pdo2) + 1;
		
		if(!$this->Trigger($pdo2))
		{
			if ($pdo == null)
				$pdo2->rollBack();
			return false;
		}
		
		if (!parent::insert("ACC_docs", $this, $pdo2)) {
			if ($pdo == null)
				$pdo2->rollBack();
			return false;
		}

		$this->DocID = parent::InsertID($pdo2);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->DocID;
		$daObj->TableName = "ACC_docs";
		$daObj->execute($pdo2);

		if ($pdo == null)
			$pdo2->commit();
		return true;
	}

	function Edit($pdo = null) {
		
		if(!$this->Trigger($pdo))
			return false;
		
		if (parent::update("ACC_docs", $this, " DocID=:did", array(":did" => $this->DocID), $pdo) === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->DocID;
		$daObj->TableName = "ACC_docs";
		$daObj->execute($pdo);
		
		return true;
	}

	static function Remove($DocID, $pdo = null) {
		
		$temp = parent::runquery("select * from ACC_docs join ACC_cycles using(CycleID)
			where DocID=?", array($DocID));
		if (count($temp) == 0)
			return false;

		if($temp[0]["DocStatus"] != "RAW")
		{
			ExceptionHandler::PushException("سند مربوطه تایید شده و قابل حذف نمی باشد");
			return false;
		}
		if($temp[0]["IsClosed"] == "YES")
		{
			ExceptionHandler::PushException("دوره مربوطه بسته شده و سند قابل حذف نمی باشد.");
			return false;
		}
		
		if($pdo == null)
		{
			$pdo2 = parent::getPdoObject();
			$pdo2->beginTransaction();
		}
		else
			$pdo2 = $pdo;
		$result = parent::delete("ACC_DocCheques", "DocID=?", array($DocID), $pdo2);
		if ($result === false)
		{
			$pdo2->rollBack();
			return false;
		}

		$result = parent::delete("ACC_DocItems", "DocID=?", array($DocID), $pdo2);
		if ($result === false)
		{
			$pdo2->rollBack();
			return false;
		}

		$result = parent::delete("ACC_docs", "DocID=?", array($DocID), $pdo2);
		if ($result === false)
		{
			$pdo2->rollBack();
			return false;
		}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $DocID;
		$daObj->TableName = "ACC_docs";
		$daObj->execute($pdo2);

		if($pdo == null)
			$pdo2->commit();
		return true;	
	}
	
	static function GetLastLocalNo(){
		
		$no = parent::GetLastID("ACC_docs", "LocalNo", "CycleID=? AND BranchID=?", 
			array($_SESSION["accounting"]["CycleID"],$_SESSION["accounting"]["BranchID"]));
		
		return $no+1;
	}

}

class ACC_DocItems extends PdoDataAccess {

	public $DocID;
	public $ItemID;
	public $CostID;
	public $TafsiliType;
	public $TafsiliID;
	public $TafsiliType2;
	public $TafsiliID2;
	public $DebtorAmount;
	public $CreditorAmount;
	public $details;
	public $locked;
	public $SourceType;
	public $SourceID;
	public $SourceID2;
	public $SourceID3;
	
	function __construct() {
	}

	static function GetAll($where = "", $whereParam = array()) {
		
		$query = "select si.*,cc.CostCode,concat_ws('-',b1.blockDesc,b2.BlockDesc,b3.BlockDesc) CostDesc,
			t.TafsiliDesc,bi.InfoDesc TafsiliGroupDesc,
			t2.TafsiliDesc as Tafsili2Desc,bi2.InfoDesc Tafsili2GroupDesc
		from ACC_DocItems si
			left join ACC_CostCodes cc using(CostID)
			left join ACC_blocks b1 on(cc.level1=b1.blockID)
			left join ACC_blocks b2 on(cc.level2=b2.blockID)
			left join ACC_blocks b3 on(cc.level3=b3.blockID)
			left join BaseInfo bi on(si.TafsiliType=InfoID AND TypeID=2)
			left join BaseInfo bi2 on(si.TafsiliType2=bi2.InfoID AND bi2.TypeID=2)
			left join ACC_tafsilis t on(t.TafsiliID=si.TafsiliID)
			left join ACC_tafsilis t2 on(t2.TafsiliID=si.TafsiliID2)
			";
		$query .= ($where != "") ? " where " . $where : "";
		return parent::runquery_fetchMode($query, $whereParam);
	}

	function Add($pdo = null) {
		
		if($this->CostID == COSTID_share)
		{
			$amount = $this->CreditorAmount > 0 ? $this->CreditorAmount : $this->DebtorAmount;
			if($amount*1 % ShareBaseAmount != 0)
			{
				ExceptionHandler::PushException("مبلغ سرفصل حساب سهام باید مضربی از " . ShareBaseAmount . " باشد");
				return false;
			}
		}
		
		if (!parent::insert("ACC_DocItems", $this, $pdo))
			return false;
		
		$this->ItemID = parent::InsertID($pdo);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->ItemID;
		$daObj->TableName = "ACC_DocItems";
		$daObj->execute($pdo);
		return true;
	}

	function Edit() {
		if (!parent::update("ACC_DocItems", $this, "ItemID=:rid", 
				array(":rid" => $this->ItemID)))
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->ItemID;
		$daObj->TableName = "ACC_DocItems";
		$daObj->execute();
		return true;
	}

	static function Remove($ItemID) {
		
		$result = parent::delete("ACC_DocItems", "ItemID=:rid",array(":rid" => $ItemID));

		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $ItemID;
		$daObj->TableName = "ACC_DocItems";
		$daObj->execute();
		return true;
	}
}

class ACC_DocCheques extends PdoDataAccess {
    public $ChequeID;
    public $DocID;
	public $CheckNo;
	public $AccountID;
	public $CheckDate;
	public $amount;
	public $CheckStatus;
	public $TafsiliID;
	public $description;

    function __construct()
    {
		$this->DT_CheckDate = DataMember::CreateDMA(DataMember::DT_DATE);
    }

    static function GetAll($where = "",$whereParam = array())
    {
	    $query = "select c.*,a.*,b.infoDesc as StatusTitle, bk.BankDesc, t.TafsiliDesc
					from ACC_DocCheques c
					left join ACC_accounts a using(AccountID)
					left join ACC_banks bk on(a.BankID=bk.BankID)
					left join ACC_tafsilis t on(TafsiliType=" . TAFTYPE_PERSONS . " AND t.TafsiliID=c.TafsiliID)
					join BaseInfo b on(b.typeID=4 AND b.InfoID=CheckStatus)
			";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery($query, $whereParam);
    }

    function Add()
    {
	    if( parent::insert("ACC_DocCheques", $this) === false )
		    return false;

	    $this->ChequeID = parent::InsertID();

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->ChequeID;
		$daObj->TableName = "ACC_DocCheques";
		$daObj->execute();
		return true;
    }

    function Edit()
    {
	    $whereParams = array();
	    $whereParams[":kid"] = $this->ChequeID;

	    if( parent::update("ACC_DocCheques",$this," ChequeID=:kid", $whereParams) === false )
		    return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->ChequeID;
		$daObj->TableName = "ACC_DocCheques";
		$daObj->execute();
		return true;
    }

    static function Remove($ChequeID)
    {
	    $result = parent::delete("ACC_DocCheques", "ChequeID=:kid ",
		    array(":kid" => $ChequeID));

	    if($result === false)
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $ChequeID;
		$daObj->TableName = "ACC_DocCheques";
		$daObj->execute();
		return true;
    }
}

?>