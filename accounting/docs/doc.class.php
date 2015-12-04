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
	public $description;
	public $regPersonID;
		
	function __construct($DocID = "") {
		
		$this->DT_DocDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_RegDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($DocID != "")
			parent::FillObject ($this, "select * from ACC_docs where DocID=?", array($DocID));
	}

	static function GetAll($where = "", $whereParam = array()) {
		
		$query = "select sd.*, concat(fname,' ',lname) as regPerson
			from ACC_docs sd
			join BSC_persons p on(regPersonID=PersonID)";
		
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
				ExceptionHandler::PushException("تاریخ برگه باید از برگه های بعدی کوچکتر باشد.");
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
					ExceptionHandler::PushException("تاریخ برگه باید از برگه های قبلی بزرگتر باشد.");
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

	static function Remove($DocID) {
		
		$temp = parent::runquery("select * from ACC_docs where DocID=?", array($DocID));
		if (count($temp) == 0)
			return false;

		if ($temp[0]["DocStatus"] == "RAW") {
			
			$pdo = parent::getPdoObject();
			$pdo->beginTransaction();
			
			$result = parent::delete("ACC_DocChecks", "DocID=?", array($DocID), $pdo);
			if ($result === false)
			{
				$pdo->rollBack();
				return false;
			}
			
			$result = parent::delete("ACC_DocItems", "DocID=?", array($DocID), $pdo);
			if ($result === false)
			{
				$pdo->rollBack();
				return false;
			}

			$result = parent::delete("ACC_docs", "DocID=?", array($DocID), $pdo);
			if ($result === false)
			{
				$pdo->rollBack();
				return false;
			}

			$daObj = new DataAudit();
			$daObj->ActionType = DataAudit::Action_delete;
			$daObj->MainObjectID = $DocID;
			$daObj->TableName = "ACC_docs";
			$daObj->execute($pdo);

			$pdo->commit();
			return true;
		}

		$result = parent::runquery("update ACC_docs set DocStatus='DELETED'
				where DocID=:did ", array(":did" => $DocID));

		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $DocID;
		$daObj->TableName = "ACC_docs";
		$daObj->execute();
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
	public $Tafsili2Type;
	public $Tafsili2ID;
	public $DebtorAmount;
	public $CreditorAmount;
	public $details;
	public $locked;
	public $SourceType;
	public $SourceID;
	public $SourceID2;
	
	function __construct() {
	}

	static function GetAll($where = "", $whereParam = array()) {
		
		$query = "select si.*,concat_ws('-',b1.blockDesc,b2.BlockDesc,b3.BlockDesc) CostDesc,
			t.TafsiliDesc,bi.InfoDesc TafsiliGroupDesc,
			t2.TafsiliDesc as Tafsili2Desc,bi2.InfoDesc Tafsili2GroupDesc
		from ACC_DocItems si
			left join ACC_CostCodes cc using(CostID)
			left join ACC_blocks b1 on(cc.level1=b1.blockID)
			left join ACC_blocks b2 on(cc.level2=b2.blockID)
			left join ACC_blocks b3 on(cc.level3=b3.blockID)
			left join BaseInfo bi on(TafsiliType=InfoID AND TypeID=2)
			left join BaseInfo bi2 on(Tafsili2Type=bi2.InfoID AND bi2.TypeID=2)
			left join ACC_tafsilis t on(t.TafsiliID=si.TafsiliID)
			left join ACC_tafsilis t2 on(t2.TafsiliID=si.Tafsili2ID)
			";
		$query .= ($where != "") ? " where " . $where : "";
		return parent::runquery_fetchMode($query, $whereParam);
	}

	function Add($pdo = null) {
		
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

class ACC_DocChecks extends PdoDataAccess {
    public $CheckID;
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
					from ACC_DocChecks c
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
	    if( parent::insert("ACC_DocChecks", $this) === false )
		    return false;

	    $this->CheckID = parent::InsertID();

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->CheckID;
		$daObj->TableName = "ACC_DocChecks";
		$daObj->execute();
		return true;
    }

    function Edit()
    {
	    $whereParams = array();
	    $whereParams[":kid"] = $this->CheckID;

	    if( parent::update("ACC_DocChecks",$this," CheckID=:kid", $whereParams) === false )
		    return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->CheckID;
		$daObj->TableName = "ACC_DocChecks";
		$daObj->execute();
		return true;
    }

    static function Remove($CheckID)
    {
	    $result = parent::delete("ACC_DocChecks", "CheckID=:kid ",
		    array(":kid" => $CheckID));

	    if($result === false)
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $CheckID;
		$daObj->TableName = "ACC_DocChecks";
		$daObj->execute();
		return true;
    }
}

?>