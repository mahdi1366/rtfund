<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.08
//---------------------------

class manage_staff_tax extends PdoDataAccess
{
    public $tax_history_id ;
	public $staff_id;
	public $tax_table_type_id;
	public $start_date;
    public $end_date;
    public $payed_tax_value;
    
	public function  __construct($staff_id="") {

		if($staff_id!="")
		{
			$whereParam = array(":staff_id" => $staff_id);
			$query = "select * from staff_tax_history where staff_id=:staff_id AND payed_tax_value IS NOT NULL ";
			PdoDataAccess::FillObject($this, $query, $whereParam);
		}

		$this->DT_start_date = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_end_date = DataMember::CreateDMA(DataMember::DT_DATE);
	}

    private function date_overlap($PID)
	 {
        $whereParam = array();
        $whereParam[":pid"] = $PID ;
        
		$query = "select  count(*) cn
							from staff_tax_history sth inner join staff s
																on sth.staff_id = s.staff_id
														   inner join persons p
																on s.personid = p.personid
									where
											  p.personid =:pid  and payed_tax_value is null  and ( sth.end_date is null or sth.end_date = '0000-00-00' )
                                  ";

        $temp = PdoDataAccess::runquery($query,$whereParam);

		if( $temp[0][0] > 0  && $this->tax_history_id == null )
		{
			parent::PushException("لطفا ابتدا تاریخ انتهای رکورد قبل را پر نمایید.");
			return false;
		}


        $query = "	select count(*)
							from staff_tax_history sth inner join staff s
																on sth.staff_id = s.staff_id
														   inner join persons p
																on s.personid = p.personid
									where
											((sth.start_date <=:fdate AND sth.end_date>=:fdate) OR (sth.start_date>=:fdate AND sth.start_date <=:tdate)) AND
											  p.personid =:pid  ";


		
		$whereParam[":fdate"] = str_replace('/','-',DateModules::shamsi_to_miladi($this->start_date));
		$whereParam[":tdate"] = str_replace('/','-',DateModules::shamsi_to_miladi($this->end_date));
		
		if($this->tax_history_id ){
		   $query .= " and tax_history_id  != :ihid ";
		   $whereParam[":ihid"] = $this->tax_history_id ;
		   }

		$temp = PdoDataAccess::runquery($query, $whereParam);

		if($temp[0][0] != 0)
		{
			parent::PushException(ER_DATE_RANGE_OVERLAP);
			return false;
		}
        
		return true ;
	 }


	function SaveStaffTaxHistory($PID){

        if(!$this->date_overlap($PID))
			return false ;

	 	if( PdoDataAccess::insert("HRM_staff_tax_history", $this) === false )
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->RelatedPersonType = 3 ;
		$daObj->RelatedPersonID = $this->staff_id;
		$daObj->TableName = "staff_tax_history";
		$daObj->execute();

		return true;
		
	}

	function EditStaffTaxHistory($PID){

        if(!$this->date_overlap($PID))
			return false ;

		$whereParams = array();
	 	$whereParams[":sid"] = $this->staff_id;
		$whereParams[":taxid"] = $this->tax_history_id ;

	 	if( PdoDataAccess::update("HRM_staff_tax_history",$this," staff_id=:sid  and tax_history_id = :taxid ", $whereParams) === false)
			return false;
			
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->RelatedPersonType = 3;
		$daObj->RelatedPersonID = $this->staff_id;		
		$daObj->TableName = "staff_tax_history";
		$daObj->execute();

	 	return true;

	}

	static function GetAllStaffTaxHistory($personid){

		$query = " select sth.* , 'روز مزد بیمه ای' person_type, p.personid 
						from HRM_staff_tax_history sth inner join HRM_staff s on sth.staff_id = s.staff_id 
											inner join HRM_persons p on p.personid = s.personid
                                             
                                                        where p.personid  = ". $personid ." and payed_tax_value IS NULL";

		$temp = PdoDataAccess::runquery($query);

		return $temp;	
	}

	function Remove()
	{
		$result = parent::delete("HRM_staff_tax_history", " staff_id = :SID and tax_history_id = :ID ",
							array(":SID" => $this->staff_id,
								  ":ID" => $this->tax_history_id));
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->RelatedPersonID = $this->staff_id;
		$daObj->RelatedPersonType = 3;
		$daObj->MainObjectID = $this->tax_history_id;
		$daObj->TableName = "staff_tax_history";
		$daObj->execute();

		return true;
	}

	
}


?>