<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

class ACC_blocks extends PdoDataAccess{
	
    public $BlockID;
	public $LevelID;
	public $BlockCode;
	public $BlockDesc;
	public $essence;
	public $GroupID;

    function __construct($BlockID = "")
    {
		if($BlockID != "")
			parent::FillObject($this, "select * from ACC_blocks where BlockID=?", array($BlockID));
    }

    static function GetAll($where = "",$whereParam = array())
    {
	    $query = "select b.* from ACC_blocks b";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery_fetchMode($query, $whereParam);
    }

    function AddBlock()
    {
		if($this->BlockID == "")
			unset($this->BlockID);
		
	    if( parent::insert("ACC_blocks", $this) === false )
		    return false;

	    $this->BlockID = parent::InsertID();

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->BlockID;
		$daObj->TableName = "ACC_blocks";
		$daObj->execute();
		return true;	
    }

    function EditBlock()
    {
	    $whereParams = array();
	    $whereParams[":kid"] = $this->BlockID;

	    if( parent::update("ACC_blocks",$this," BlockID=:kid", $whereParams) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->BlockID;
		$daObj->TableName = "ACC_blocks";
		$daObj->execute();
		return true;	
    }

    static function RemoveBlock($BlockID)
    {
	    $result = parent::delete("ACC_blocks", "BlockID=:kid ",
		    array(":kid" => $BlockID));

	    if($result === false)
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $BlockID;
		$daObj->TableName = "ACC_blocks";
		$daObj->execute();
		return true;	
    }
}

class ACC_CostCodes extends PdoDataAccess {

    public $CostID;
    public $level1;
    public $level2;
    public $level3;
    public $IsActive;
    public $CostCode;
	public $TafsiliType;
	public $TafsiliType2;

    function __construct($CstID = '') {

        if ($CstID == '')
            return;
        parent::FillObject($this, "select * from ACC_CostCodes where CostID=:CstId", array(':CstId' => $CstID));
    }

    function InsertCost($pdo = null) {

        if ($pdo == null) {
            $db = parent::getPdoObject();
            $db->beginTransaction();
        } else {
            $db = $pdo;
        }
		
        $res = parent::insert("ACC_CostCodes", $this, $db);
        if ($res === false) {
			
			if ($pdo == null)
				$db->rollBack();
            return false;
        }

        $this->CostID = parent::InsertID($db);
		
        $query = "update ACC_CostCodes c 
			left join ACC_blocks b1 on(b1.levelID=1 AND b1.blockID=c.level1)
			left join ACC_blocks b2 on(b2.levelID=2 AND b2.blockID=c.level2)
			left join ACC_blocks b3 on(b3.levelID=3 AND b3.blockID=c.level3)
			set c.CostCode=concat_ws('-', b1.blockCode,b2.BlockCode,b3.BlockCode)
			where CostID=?";
        $res = parent::runquery($query, array($this->CostID), $db);
        if ($res === false) {
            if ($pdo == null)
				$db->rollBack();
            return false;
        }
		
		$dt = PdoDataAccess::runquery("select * from ACC_CostCodes c1 
			join ACC_CostCodes c2 on(c2.IsActive='YES' AND c1.CostID<>c2.CostID AND c1.CostCode=c2.CostCode)
			where c1.CostID=? ", array($this->CostID), $db);
		
		if (count($dt) > 0) {
			
            if ($pdo == null)
				$db->rollBack();
			parent::PushException("کد حساب تکراری است");
            return false;
        }
		
        $auditObj = new DataAudit();
        $auditObj->ActionType = DataAudit::Action_add;
        $auditObj->MainObjectID = $this->CostID;
        $auditObj->TableName = "ACC_CostCodes";
        $auditObj->execute($db);

        if ($pdo == null)
            $db->commit();

        return true;
    }

	function UpdateCost($pdo = null) {

        if ($pdo == null) {
            $db = parent::getPdoObject();
            $db->beginTransaction();
        } else {
            $db = $pdo;
        }
		$dt = parent::runquery("select * from ACC_DocItems where CostID=?", array($this->CostID));
		if(count($dt) > 0)
		{
			$this->DeleteCost($db);
			unset($this->CostID);
			$result = $this->InsertCost($db);
			if($result)
				$db->commit();
			return $result;
		}
		
		$dt = PdoDataAccess::runquery("select c2.* from ACC_CostCodes c1 
			join ACC_CostCodes c2 on(c2.IsActive='YES' AND c1.CostID<>c2.CostID AND c1.CostCode=c2.CostCode)
			where c1.CostID=?", array($this->CostID), $db);
		if (count($dt) > 0) {
			
            if ($pdo == null)
				$db->rollBack();
			parent::PushException("کد حساب تکراری است");
            return false;
        }
		
        $res = parent::update("ACC_CostCodes", $this,"CostID=:c", array(":c" => $this->CostID), $db);

        if ($res === false) {
			
			if ($pdo == null)
				$db->rollBack();
            return false;
        }

        $query = "update ACC_CostCodes c 
			left join ACC_blocks b1 on(b1.levelID=1 AND b1.blockID=c.level1)
			left join ACC_blocks b2 on(b2.levelID=2 AND b2.blockID=c.level2)
			left join ACC_blocks b3 on(b3.levelID=3 AND b3.blockID=c.level3)
			set c.CostCode=concat_ws('-', b1.blockCode,b2.BlockCode,b3.BlockCode)
			where CostID=?";
        $res = parent::runquery($query, array($this->CostID), $db);
        if ($res === false) {
            if ($pdo == null)
				$db->rollBack();
            return false;
        }
		
        $auditObj = new DataAudit();
        $auditObj->ActionType = DataAudit::Action_update;
        $auditObj->MainObjectID = $this->CostID;
        $auditObj->TableName = "ACC_CostCodes";
        $auditObj->execute($db);

        if ($pdo == null)
            $db->commit();

        return true;
    }
	
	function ActiveCode(){
		
		$dt = PdoDataAccess::runquery("select c2.* from ACC_CostCodes c1 
			join ACC_CostCodes c2 on(c2.IsActive='YES' AND c1.CostID<>c2.CostID AND c1.CostCode=c2.CostCode)
			where c1.CostID=?", array($this->CostID));
		if (count($dt) > 0) {
			parent::PushException("کد حساب تکراری است");
            return false;
        }
		$this->IsActive = "YES";
		if(!parent::update("ACC_CostCodes", $this,"CostID=:c", array(":c" => $this->CostID)))
			return false;

		$auditObj = new DataAudit();
        $auditObj->ActionType = DataAudit::Action_update;
        $auditObj->MainObjectID = $this->CostID;
        $auditObj->TableName = "ACC_CostCodes";
        $auditObj->execute();
	}
	
    function DeleteCost($pdo = null) {

        $res = parent::delete("ACC_CostCodes", 'CostID=:CstId', array(':CstId' => $this->CostID),$pdo);

        if ($res === false) {
			parent::PopAllExceptions();
            parent::runquery("update ACC_CostCodes set IsActive='NO' where CostID=:CstId", 
					array(':CstId' => $this->CostID), $pdo);
            return false;
        }

        $auditObj = new DataAudit();
        $auditObj->ActionType = DataAudit::Action_delete;
        $auditObj->MainObjectID = $this->CostID;
        $auditObj->TableName = "ACC_CostCodes";
        $auditObj->execute($pdo);

        return true;
    }

    static function SelectCost($where = '', $param = array()) {

        $query = "select cc.*,
					b0.BlockDesc LevelTitle0,
					b1.BlockDesc LevelTitle1,
					b2.BlockDesc LevelTitle2,
                    b3.BlockDesc LevelTitle3,
                    concat_ws('-',b1.blockdesc,b2.blockdesc,b3.blockdesc) as CostDesc,
					bf1.InfoDesc TafsiliTypeDesc,
					bf2.InfoDesc TafsiliTypeDesc2
					
                    from ACC_CostCodes as cc
					left join ACC_blocks b1 on(b1.BlockID=cc.Level1)
					left join ACC_blocks b0 on(b1.GroupID=b0.BlockID)
                    left join ACC_blocks b2 on(b2.BlockID=cc.Level2)
                    left join ACC_blocks b3 on(b3.BlockID=cc.Level3)
					left join BaseInfo bf1 on(bf1.TypeID=2 AND cc.TafsiliType=bf1.InfoID)
					left join BaseInfo bf2 on(bf2.TypeID=2 AND cc.TafsiliType2=bf2.InfoID)";
        if ($where != '')
            $query .= ' where ' . $where;

        return parent::runquery_fetchMode($query, $param);
    }

   /* static function GetCostRemainder($UnitID, $PeriodID, $CostID = '', $LevelID = '', $BlockID = '', $PreLevelsStr = '') {
        if (empty($LevelID)) {
            $query = "select CostID,CostCode,blockEssence,HasArchiveNo,sum(ifnull(DebtorAmount,0)) debtor,sum(ifnull(CreditorAmount,0)) creditor
                    from ACC_CostCodes cc
                    left join DocItems di using(CostID)
                    left join DocHeaders dh using(DocID)
                    left join CostBlocks on(levelID=2 AND level2=BlockID)

                    where CostID=? AND dh.UnitID=? AND dh.PeriodID=?

                    group by CostID";

            $dt = PdoDataAccess::runquery($query, array($CostID, $UnitID, $PeriodID));
        } else {

            $query = "select CostID,CostCode,sum(ifnull(DebtorAmount,0)) debtor,sum(ifnull(CreditorAmount,0)) creditor
                    from ACC_CostCodes cc
                    left join DocItems di using(CostID)
                    left join DocHeaders dh using(DocID)
                    left join CostBlocks b1 on b1.blockid=cc.Level1 and b1.LevelID=1 and (cc.level1 is not null) 
                    left join CostBlocks b2 on b2.blockid=cc.Level2 and b2.LevelID=2 and (cc.level2 is not null) 
                    left join CostBlocks b3 on b3.blockid=cc.Level3 and b3.LevelID=3 and (cc.level3 is not null) 
                    left join CostBlocks b4 on b4.blockid=cc.Level4 and b4.LevelID=4 and (cc.level4 is not null) 
                    left join CostBlocks b5 on b5.blockid=cc.Level5 and b5.LevelID=5 and (cc.level5 is not null) 

                    where b" . $LevelID . ".BlockID=?  $PreLevelsStr AND dh.UnitID=? AND dh.PeriodID=?

                    group by b" . $LevelID . ".BlockID";

            $dt = PdoDataAccess::runquery($query, array($BlockID, $UnitID, $PeriodID));
        }
        if (count($dt) == 0)
            return 0;
        return array("amount" => $dt[0]["debtor"] - $dt[0]["creditor"],
            "essence" => $dt[0]["blockEssence"], "HasArchiveNo" => $dt[0]["HasArchiveNo"]);
    }*/

}

class ACC_tafsilis extends PdoDataAccess{
	
	public $TafsiliID;
	public $TafsiliType;
	public $TafsiliCode;
	public $TafsiliDesc;
	public $IsActive;
	public $ObjectID;
			
	function __construct($TafsiliID = "") {
		
		if($TafsiliID != "")
			PdoDataAccess::FillObject ($this, "select * from ACC_tafsilis where TafsiliID=?", array($TafsiliID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery_fetchMode(
			"select t.* from ACC_tafsilis t
				left join BSC_persons p on(t.TafsiliType=1 AND t.ObjectID=p.PersonID)

				where " . $where, $param);
	}
	
	function AddTafsili()
	{
	 	if(!parent::insert("ACC_tafsilis",$this))
			return false;
		$this->TafsiliID = parent::InsertID();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->TafsiliID;
		$daObj->TableName = "ACC_tafsilis";
		$daObj->execute();
		return true;
	}
	
	function EditTafsili()
	{
	 	if( parent::update("ACC_tafsilis",$this," TafsiliID=:l", array(":l" => $this->TafsiliID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->TafsiliID;
		$daObj->TableName = "ACC_tafsilis";
		$daObj->execute();
	 	return true;
    }
	
	static function DeleteTafsili($TafsiliID){
		
		if( parent::delete("ACC_tafsilis"," TafsiliID=?", array($TafsiliID)) === false )
		{
			parent::runquery("update ACC_tafsilis set IsActive='NO' where TafsiliID=?", array($TafsiliID));
		}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $TafsiliID;
		$daObj->TableName = "ACC_tafsilis";
		$daObj->execute();
	 	return true;
	}
}

class ACC_banks extends PdoDataAccess {

    public $BankID;
    public $BankDesc;
	public $IsActive;

    function __construct($BId = '') {
        parent::__construct();
        if ($BId == '')
            return;
        PdoDataAccess::FillObject($this, "select * from ACC_banks where BankID=?", array($BId));
    }
	
    function InsertBank() {

        $res = parent::insert("ACC_banks", $this);
        if ($res === false) 
            return false;

        $this->BankID = parent::InsertID();

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_add;
        $daObj->MainObjectID = $this->BankID;
        $daObj->TableName = "ACC_banks";
        $daObj->execute();

        return true;
    }

    function UpdateBank() {

        $res = parent::update("ACC_banks", $this, 'BankID=:BId', array(':BId' => $this->BankID));
        if ($res === false)
			return false;

        if (parent::AffectedRows()) {
            $daObj = new DataAudit();
            $daObj->ActionType = DataAudit::Action_update;
            $daObj->MainObjectID = $this->BankID;
            $daObj->TableName = "ACC_banks";
            $daObj->execute();
        }

        return true;
    }

    function DeleteBank() {

        $res = parent::delete("ACC_banks", 'BankID=:BId', array(':BId' => $this->BankID));
        if ($res === false) 
            return false;

        if (parent::AffectedRows()) {
            $daObj = new DataAudit();
            $daObj->ActionType = DataAudit::Action_delete;
            $daObj->MainObjectID = $this->BankID;
            $daObj->TableName = "ACC_banks";
            $daObj->execute();
        }

        return true;
    }

    public static function SelectBanks($where = '', $param = array()) {
        $query = "select * from ACC_banks ";
        if ($where != '')
            $query .= ' where ' . $where;
        $query .= ' order by BankID desc ';
        return parent::runquery_fetchMode($query, $param);
    }
}

class ACC_accounts extends PdoDataAccess {

    public $AccountID;
    public $BankID;
    public $AccountDesc;
	public $AccountNo;
    public $AccountType;
    public $IsActive;
	public $shaba;

	public $_BankDesc;
	
    function __construct($aid = '') {

        if ($aid == '')
            return;
        return parent::FillObject($this, "select ba.*,BankDesc as _BankDesc
			from ACC_accounts ba 
			join ACC_banks using(BankID) 
			where AccountID=:BAId", array(':BAId' => $aid));
    }

    public static function SelectAccounts($where = '', $param = array()) {

        $query = " select * from ACC_accounts acc ";
        if ($where != '')
            $query .= ' where ' . $where;
        $res = parent::runquery_fetchMode($query, $param);
        return $res;
    }

	public function AccountNoValidity(){
		
		$dt = parent::runquery("select * from ACC_accounts where AccountNo=? AND AccountID<>?", 
			array($this->AccountNo, $this->AccountID));
		
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("کد حساب تکراری است");
			return false;
		}
		
		return true;
	}
	
    function InsertAccount() {

		if(!$this->AccountNoValidity())
			return false;
		
		if (!parent::insert("ACC_accounts", $this))
            return false;

        $this->AccountID = parent::InsertID();

		$obj = new ACC_tafsilis();
		$obj->ObjectID = $this->AccountID;
		$obj->TafsiliCode = $this->AccountNo;
		$obj->TafsiliDesc = $this->AccountDesc;
		$obj->TafsiliType = "3";
		$obj->AddTafsili();
		
        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_add;
        $daObj->MainObjectID = $this->AccountID;
        $daObj->SubObjectID = $this->BankID;
        $daObj->TableName = "ACC_accounts";
        $daObj->execute();

        return true;
    }

    function UpdateAccount() {
		
		if(!$this->AccountNoValidity())
			return false;

        if (!parent::update("ACC_accounts", $this, 'AccountID=:ACId', array(':ACId' => $this->AccountID)))
            return false;

		$dt = PdoDataAccess::runquery("select * from ACC_tafsilis where ObjectID=? AND TafsiliType=3", array($this->AccountID));
		if(count($dt) == 0)
		{
			$obj = new ACC_tafsilis();
			$obj->ObjectID = $this->AccountID;
			$obj->TafsiliCode = $this->AccountNo;
			$obj->TafsiliDesc = $this->AccountDesc;
			$obj->TafsiliType = "3";
			$obj->AddTafsili();
		}
		else
		{
			$obj = new ACC_tafsilis($dt[0]["TafsiliID"]);
			$obj->TafsiliCode = $this->AccountNo;
			$obj->TafsiliDesc = $this->AccountDesc;
			$obj->EditTafsili();
		}
		
		
        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_update;
        $daObj->MainObjectID = $this->AccountID;
        $daObj->TableName = "ACC_accounts";
        $daObj->execute();

        return true;
    }

    static function DeleteAccount($AccountID) {

		parent::runquery("update ACC_accounts set IsActive='NO' where AccountID=?", array($AccountID));

		if (ExceptionHandler::GetExceptionCount() <> 0)
            return false;
		
		parent::runquery("update ACC_tafsilis set IsActive='NO' where TafsiliType=3 
			AND ObjectID=?", array($AccountID));

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_delete;
        $daObj->MainObjectID = $AccountID;
        $daObj->TableName = "ACC_accounts";
        $daObj->execute();

        return true;
    }

}

class ACC_cheques extends PdoDataAccess {

	public $ChequeID;
	public $AccountID;
	public $SerialNo;
	public $MinNo;
	public $MaxNo;
	public $IsActive;

	function __construct($CId = '') {

		if ($CId == '')
			return;
		return parent::FillObject($this, "select * from ACC_cheques where ChequeID=?", array($CId));
	}

	function InsertCheque($pdo=null) {
		
		if (parent::insert("ACC_cheques", $this, $pdo) === false)
			return false;
		
		$this->ChequeID = parent::InsertID($pdo);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->ChequeID;
		$daObj->TableName = "ACC_cheques";
		$daObj->execute($pdo);
		return true;
	}

	function UpdateCheque($pdo=null) {
		
            $whereParams = array();
            $whereParams[":ChequeID"] = $this->ChequeID;
            if (parent::update("ACC_cheques", $this, " ChequeID=:ChequeID", $whereParams) === false)
                return false;
            $daObj = new DataAudit();
            $daObj->ActionType = DataAudit::Action_update;
            $daObj->MainObjectID = $this->ChequeID;
            $daObj->TableName = "ACC_cheques";
            $daObj->execute($pdo=null);
            return true;
        }

	function DeleteCheque(){
		
		$whereParams = array();
		$whereParams[":ChequeID"] = $this->ChequeID;
		$result = parent::delete("ACC_cheques", "ChequeID=:ChequeID", $whereParams);
		if ($result === false)
			return false;
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $this->ChequeID;
		$daObj->TableName = "ACC_cheques";
		$daObj->execute();
		return true;
	}

	static function SelectCheques($where = '', $param = array()) {

		$query = "select c.* from ACC_cheques c ";
		if ($where != '')
			$query .= ' where ' . $where;
		$query .= ' order by ChequeID desc ';
		
		return parent::runquery($query, $param);
	}

	function IsValidNumbering() {
		
		if ($this->AccountID == '' ||
				$this->MinNo == '' ||
				$this->MaxNo == '') {
			$this->PushException("اطلاعات ناقص می باشد");
			return false;
		}
		$res = parent::runquery("select ChequeID
  	                         from ACC_cheques
  	                        
                             where AccountID =(select AccountID from ACC_accounts where AccountID=:BAId)
                             and (
                                  (MinNo <= :min and MaxNo >= :min)
                                  or
                                  (MinNo <= :max and MaxNo >= :max)
                                  or
                                  (MinNo >= :min and MaxNo <= :max )
                                 )", array(':BAId' => $this->AccountID,
					':min' => $this->MinNo,
					':max' => $this->MaxNo)
		);

		if ($res === false) {
			$this->PushException(self::ERROR_DB);
			return false;
		}
		if (!$res)
			return true;
		else if (count($res) == 1 && $this->ChequeID != '' && $res[0]['ChequeID'] == $this->ChequeID)
			return true;
		return false;
	}
}

class ACC_roles extends OperationClass {

	const TableName = "ACC_roles";
	const TableKey = "RowID";
	
	public $RowID;
	public $RoleID;
	public $PersonID;
	
	static function GetUserRole($PersonID){
		
		$dt = PdoDataAccess::runquery("select * from ACC_roles where PersonID=? order by RoleID desc",
			array($PersonID));
		
		return count($dt) == 0 ? "" : $dt[0]["RoleID"];
	}

}
?>