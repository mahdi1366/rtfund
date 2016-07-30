<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.06
//---------------------------

class manage_confirmation extends PdoDataAccess
{
	public $SalaryItemReportID ;
	public $SalaryItemTitle;
	public $description;
        public $BeneficiaryID ;
        public $ItemValue ; 
        public $PayYear ; 
        public $PayMonth ; 
        public $PersonType ; 
        public $ItemType ; 
       
	function __construct($SalaryItemReportID = "")
	 {
		        
	 	return;
	 }

	 static function GetAll($where)
	 {
	 	
                $query = " SELECT sir.SalaryItemReportID , sir.SalaryItemTitle, sir.description, sir.BeneficiaryID, sir.ItemValue ,sir.PayYear ,sir.PayMonth , sir.PersonType ,
                                  sir.ItemType , b.title ,  bi.Title person_type_title 

                                            FROM SalaryItemReport sir inner join beneficiary b on sir.BeneficiaryID = b.BeneficiaryID
                                                                inner join Basic_Info bi

                                                                        on sir.PersonType = bi.InfoID and bi.typeid = 16
                                                                        
                                                                        order by sir.PayYear  DESC ,sir.PayMonth DESC
                                    " ; 

	 	return parent::runquery($query);
	 }

        
	function Add()
	{             
          
        	$return = parent::insert("SalaryItemReport",$this);

	 	if($return === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->SalaryItemReportID;
                $daObj->SubObjectID = $this->PersonType ; 
		$daObj->TableName = "SalaryItemReport";
		$daObj->execute();

		return true;
	}
        
        function Edit() {
		$whereParams = array();
		$whereParams[":sid"] = $this->SalaryItemReportID;
		
		$result = parent::update("SalaryItemReport", $this, " SalaryItemReportID=:sid ", $whereParams);

		if (!$result)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->SalaryItemReportID;	
		$daObj->TableName = "SalaryItemReport";
		$daObj->execute();

		return true;
	}		

	static function Remove($sid)
	{
            
                    $result = parent::delete("SalaryItemReport","SalaryItemReportID=:SID ",
                                                                            array(":SID" => $sid ));                

                        if(!$result)
                                return false;

                        $daObj = new DataAudit();
                        $daObj->ActionType = DataAudit::Action_delete;
                        $daObj->MainObjectID = $sid;                        
                        $daObj->TableName = "SalaryItemReport";
                        $daObj->execute();

                        return true;
        }

}

?>