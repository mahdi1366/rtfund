<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

require_once getenv("DOCUMENT_ROOT") . '/accounting/baseinfo/baseinfo.class.php';

class LON_loans extends PdoDataAccess
{
	public $LoanID;
	public $GroupID;
	public $LoanDesc;
	public $MaxAmount;
	public $InstallmentCount;
	public $IntervalType;
	public $PayInterval;
	public $DelayMonths;
	public $ForfeitPercent;
	public $CustomerWage;
	public $BlockID;
	public $IsCustomer;
	public $IsPlan;
	public $IsActive;
	public $_BlockCode;
			
	function __construct($LoanID = "") {
		
		if($LoanID != "")
			PdoDataAccess::FillObject ($this, "select l.*,b.BlockCode _BlockCode
				from LON_loans l join ACC_blocks b using(BlockID) where LoanID=?", array($LoanID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery_fetchMode("select l.*,InfoDesc GroupDesc from LON_loans l
			join BaseInfo bf on(bf.TypeID=1 AND bf.InfoID=l.GroupID)
			where " . $where, $param);
	}
	
	function AddLoan()
	{
	 	if(!parent::insert("LON_loans",$this))
			return false;
		$this->LoanID = parent::InsertID();
				
		/*$blockObj = new ACC_blocks();
		$blockObj->BlockCode = $this->LoanID;
		$blockObj->BlockDesc = $this->LoanDesc;
		$blockObj->LevelID = "2";
		$blockObj->AddBlock();
		
		$this->BlockID = $blockObj->BlockID;
		$this->EditLoan();*/
				
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;	
		$daObj->MainObjectID = $this->LoanID;
		$daObj->TableName = "LON_loans";
		$daObj->execute();
		
		$obj = new ACC_tafsilis();
		$obj->ObjectID = $this->LoanID;
		$obj->TafsiliCode = $this->LoanID;
		$obj->TafsiliDesc = $this->LoanDesc;
		$obj->TafsiliType = TAFSILITYPE_LOAN;
		$obj->AddTafsili();
		
		return true;
	}
	
	function EditLoan()
	{
	 	if( parent::update("LON_loans",$this," LoanID=:l", array(":l" => $this->LoanID)) === false )
	 		return false;

		/*$obj = new LON_loans($this->LoanID);
		$blockObj = new ACC_blocks($obj->BlockID);
		$blockObj->BlockDesc = $this->LoanDesc;
		$blockObj->EditBlock();*/
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->LoanID;
		$daObj->TableName = "LON_loans";
		$daObj->execute();
		
		$dt = PdoDataAccess::runquery("select * from ACC_tafsilis "
				. "where ObjectID=? AND TafsiliType=" . TAFSILITYPE_LOAN, array($this->LoanID));
		
		if(count($dt) == 0)
		{
			$obj = new ACC_tafsilis();
			$obj->ObjectID = $this->LoanID;
			$obj->TafsiliCode = $this->LoanID;
			$obj->TafsiliDesc =  $this->LoanDesc;
			$obj->TafsiliType = TAFSILITYPE_LOAN;
			$obj->AddTafsili();
		}
		else
		{
			$obj = new ACC_tafsilis($dt[0]["TafsiliID"]);
			$obj->TafsiliCode = $this->LoanID;
			$obj->TafsiliDesc = $this->LoanDesc;
			$obj->EditTafsili();
		}
		
	 	return true;
    }
	
	static function DeleteLoan($LoanID){
		
		if( parent::delete("LON_loans"," LoanID=?", array($LoanID)) === false )
		{
			$obj = new LON_loans($LoanID);
			$obj->IsActive = "NO";
			return $obj->EditLoan();
		}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $LoanID;
		$daObj->TableName = "LON_loans";
		$daObj->execute();
	 	return true;
	}
}

?>
