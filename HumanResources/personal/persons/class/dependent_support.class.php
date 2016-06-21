<?php
//---------------------------
// programmer:	bMahdipour
// create Date:	94.11
//---------------------------

class manage_dependent_support extends PdoDataAccess
{
	 
	public $PersonID;
	public $master_row_no;
	public $row_no;
	public $from_date;
    public $to_date;
    public $support_cause;
    public $insure_type;
	public $status;
	public $calc_year_from;
	public $calc_year_to;
	public $calc_month_from;
	public $calc_month_to;	 
	public $dana_include;

	function  __construct()
	{
		$this->DT_from_date = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_to_date = DataMember::CreateDMA(DataMember::DT_DATE);
	}

	static function GetAllDependencySupport($where="", $whereParam = array(),$info="" )
	{
		$selectedItm = " ";
		$joinqry = " " ; 
		
		if($info=="true")
		{

			$selectedItm = " , concat( bi4.title  ,' ',dsh.calc_year_from ) start_calc , concat(bi5.title ,' ',dsh.calc_year_to) end_calc ";
			
			$joinqry = "	LEFT JOIN Basic_Info bi4 ON (dsh.calc_month_from = bi4.InfoID and bi4.TypeID = 41)
						    LEFT JOIN Basic_Info bi5 ON (dsh.calc_month_to = bi5.InfoID and bi5.TypeID = 41)	";
			
			$where .= (HRSystem == SalarySystem ) ? 'AND ( status='.IN_SALARY.' OR 
				                                           status='.DELETE_IN_SALARY.' OR status='.DELETE_IN_EMPLOYEES.')' : '' ;
			
		}
				
				$query = " SELECT 	dsh.PersonID,
									dsh.master_row_no,
									dsh.row_no,
									dsh.support_cause,
									bi1.InfoDesc support_cause_title ,
									bi2.InfoDesc insure_type_title,
									dsh.insure_type,
									dsh.from_date,
									dsh.to_date,
									dsh.status,
									dsh.calc_year_from,
									dsh.calc_year_to,
									dsh.calc_month_from,
									dsh.calc_month_to 
									".$selectedItm."

							   from  HRM_person_dependent_supports dsh
									 LEFT OUTER JOIN HRM_person_dependents pd ON (dsh.PersonID = pd.PersonID AND dsh.master_row_no = pd.row_no)
									 LEFT OUTER JOIN HRM_persons p ON (pd.PersonID = p.PersonID)
									 LEFT JOIN BaseInfo bi1 ON (dsh.support_cause = bi1.InfoID and bi1.TypeID = 63)
									 LEFT JOIN BaseInfo bi2 ON (dsh.insure_type = bi2.InfoID and bi2.TypeID = 55)
									 
									 " . $joinqry  ;
				$query .= ($where != "") ? " where " . $where : "";			

		$temp = PdoDataAccess::runquery($query, $whereParam);
				
		return $temp;	
	}

	function Add()
	{
		$pdo = parent::getPdoObject();
		/*@var $pdo PDO*/
		
		$pdo->beginTransaction();

		$this->row_no = parent::GetLastID("person_dependent_supports", "row_no", "PersonID=:pid and master_row_no=:mrno", array(
			":pid" => $this->PersonID,
			":mrno" => $this->master_row_no
		)) + 1;
		$result = parent::insert("person_dependent_supports", $this);
		if($result === false)
		{
			$pdo->rollBack();
			return false;
		}

		$this->row_no = parent::InsertID();

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->MainObjectID = $this->row_no;
		$daObj->TableName = "person_dependent_supports";
		$daObj->execute();

		$pdo->commit();
		return true;
	}

	function Edit()
	{
		$result = parent::update("person_dependent_supports", $this, 
				"PersonID=:psid and master_row_no=:mrowno and row_no=:rowNo", 
				array(":psid" => $this->PersonID,
					  ":mrowno" => $this->master_row_no,
					  ":rowNo" => $this->row_no));
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->MainObjectID = $this->row_no;
		$daObj->TableName = "person_dependent_supports";
		$daObj->execute();

		return true;
	}

	
	function Remove()
	{
		$result = parent::delete("person_dependent_supports", "PersonID=:pid and master_row_no=:mrno and row_no=:rno", array(
			":pid" => $this->PersonID,
			":mrno" => $this->master_row_no,
			":rno" => $this->row_no
		));
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->MainObjectID = $this->row_no;
		$daObj->TableName = "person_dependent_supports";
		$daObj->execute();

		return true;
	}
}



?>