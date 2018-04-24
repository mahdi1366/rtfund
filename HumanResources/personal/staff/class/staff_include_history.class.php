<?php 
//---------------------------
// programmer:	B.Mahdipour
// create Date:	90.08
//---------------------------

class manage_staff_include_history extends PdoDataAccess
{
	
	public $include_history_id;
	public $staff_id;
	public $start_date;
	public $end_date;
	public $insure_include;
	public $service_include;
	public $retired_include;
	public $tax_include;
	public $pension_include;	
	
	
	function __construct($staff_id = "")
	{
		$this->DT_start_date = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_end_date = DataMember::CreateDMA(DataMember::DT_DATE);
	}

	private function date_overlap($PID)
	 {
		$query = "	select count(*)
							from HRM_staff_include_history sih inner join HRM_staff s
																on sih.staff_id = s.staff_id
														   inner join HRM_persons p
																on s.personid = p.personid
									where
											((sih.start_date <=:fdate AND sih.end_date>=:fdate) OR (sih.start_date>=:fdate AND sih.start_date <=:tdate)) AND
											  p.personid =:pid  ";
		

		$whereParam = array();
		$whereParam[":fdate"] = str_replace('/','-',DateModules::shamsi_to_miladi($this->start_date));
		$whereParam[":tdate"] = str_replace('/','-',DateModules::shamsi_to_miladi($this->end_date));
		$whereParam[":pid"] = $PID ;
		if($this->include_history_id){
		   $query .= " and include_history_id != :ihid ";
		   $whereParam[":ihid"] = $this->include_history_id ;

		   }

		$temp = PdoDataAccess::runquery($query, $whereParam);

		if($temp[0][0] != 0)
		{
			parent::PushException(ER_DATE_RANGE_OVERLAP);
			return false;
		}
		return true ;
	 }


	static function GetAllStaffIncludeHistory($personid)
	{
		$query = "	select	p.personid ,
							include_history_id,
							s.staff_id ,							
							start_date,
							end_date, 
							insure_include ,
							case insure_include
									when 1 then 'می باشد'
									when 0 then 'نمی باشد'
							end insure_include_title , 							 
							case tax_include
									when 1 then 'می باشد'
									when 0 then 'نمی باشد'
							end tax_include_title , 
							tax_include 							
							   
					from HRM_staff_include_history sih 
								inner join HRM_staff s
										on sih.staff_id = s.staff_id
								inner join HRM_persons p on p.personid = s.personid
								
					where p.personid  = ". $personid ;

		$temp = PdoDataAccess::runquery($query);
		
		return $temp;		

	}

	function Add($PID)
	{
		if(!$this->date_overlap($PID))			
			return false ;			

		$result = parent::insert("HRM_staff_include_history", $this);

		if($result === false)
		{ 
			return false;
		}
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->RelatedPersonID= $this->staff_id;
		$daObj->RelatedPersonType = 3 ;
		$daObj->TableName = "HRM_staff_include_history";
		$daObj->execute();

		return true ;
	}

	function Edit($PID)
	{
	    
	   
		if(!$this->date_overlap($PID))
			return false ;
	
		$result = parent::update("HRM_staff_include_history", $this, " staff_id = :SID and include_history_id = :ID " ,
							array(":SID" => $this->staff_id,
								  ":ID" => $this->include_history_id));
								  
								  
								  
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $this->staff_id;
		$daObj->MainObjectID = $this->include_history_id;
		$daObj->TableName = "staff_include_history";
		$daObj->execute();

		return true;
	}

	function Remove()
	{
		$query = " select s.staff_id
								from HRM_staff s inner join HRM_persons p on s.personid = p.personid and s.person_type = p.person_type
									where s.staff_id = ".$this->staff_id ;

		$temp = PdoDataAccess::runquery($query);

		if(count($temp)== 0 )
		{
			parent::PushException("حذف این رکورد امکان پذیر نمی باشد.");
			return false ;
		}
		$result = parent::delete("HRM_staff_include_history", " staff_id = :SID and include_history_id = :ID ",
							array(":SID" => $this->staff_id,
								  ":ID" => $this->include_history_id));
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->RelatedPersonID = $this->staff_id;
		$daObj->RelatedPersonType = 3 ;
		$daObj->MainObjectID = $this->include_history_id;
		$daObj->TableName = "staff_include_history";
		$daObj->execute();

		return true;
	}
	

}

?>