<?php

//---------------------------
// programmer:	Mahdipour
// create Date:	91.05.03
//---------------------------

class manage_subInfo extends PdoDataAccess {

	public $SalaryItemTypeID;
	public $PersonType;
	public $description;
	public $BeneficiaryID;
	public $arrangement ; 
	public $FromDate ; 
	public $ToDate ; 

        function __construct() {
		
		 $this->DT_FromDate = DataMember::CreateDMA(DataMember::DT_DATE);
                 $this->DT_ToDate = DataMember::CreateDMA(DataMember::DT_DATE);
		 
		return;
	}

	static function GetAll() {

		$query = " select si.SalaryItemTypeID,si.PersonType , si.description , si.BeneficiaryID , si.arrangement ,
							bf.title beneficiary_title , bi.Title person_type_title , sit.full_title , 
                                                        si.arrangement , si.FromDate , si.ToDate 
									
					from  SubtractItemInfo si 
						inner join beneficiary bf on si.BeneficiaryID = bf.BeneficiaryID
						inner join Basic_Info bi on si.PersonType = bi.InfoID and bi.typeid = 16
						inner join salary_item_types sit on si.SalaryItemTypeID = sit.salary_item_type_id
                                                order by si.arrangement";

		return parent::runquery($query);
	}

	function Add() {

		$query = " select * 
				    from SubtractItemInfo 
					where SalaryItemTypeID= " . $this->SalaryItemTypeID . " and PersonType = " . $this->PersonType." and 
					    ( ToDate is  Null OR ToDate = '0000-00-00' )";
		
                               
		$res = parent::runquery($query);
 
		if (count($res) > 0) {
		    
		    parent::PushException(strtr(IS_AVAILABLE,
                                      array("%0%" => $res[0]["arrangement"])));		    
		
			return false;
		}

		$return = parent::insert("SubtractItemInfo", $this);

		if ($return === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->SalaryItemTypeID;
		$daObj->SubObjectID = $this->PersonType;
		$daObj->TableName = "SubtractItemInfo";
		$daObj->execute();

		return true;
	}

	function Edit() {
		$whereParams = array();
		$whereParams[":sid"] = $this->SalaryItemTypeID;
		$whereParams[":ptype"] = $this->PersonType;
		$whereParams[":FD"] = DateModules::shamsi_to_miladi($this->FromDate);
		
		$result = parent::update("SubtractItemInfo", $this, " SalaryItemTypeID=:sid AND PersonType=:ptype AND FromDate=:FD ", $whereParams);

		if (!$result)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->SalaryItemTypeID;
		$daObj->SubObjectID = $this->PersonType;
		$daObj->TableName = "SubtractItemInfo";
		$daObj->execute();

		return true;
	}

	static function Remove($sid , $pt )
	{
            
                    $result = parent::delete("SubtractItemInfo","SalaryItemTypeID=:SID and  PersonType = :PT ",
                                                                            array(":SID" => $sid , 
                                                                                  ":PT" => $pt ));                

                        if(!$result)
                                return false;

                        $daObj = new DataAudit();
                        $daObj->ActionType = DataAudit::Action_delete;
                        $daObj->MainObjectID = $sid;
                        $daObj->SubObjectID = $pt;
                        $daObj->TableName = "SubtractItemInfo";
                        $daObj->execute();

                        return true;
        }

}

?>