<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once getenv("DOCUMENT_ROOT") . '/office/dms/dms.class.php';
require_once inc_CurrencyModule;

class agencyContract extends OperationClass {

    const TableName = "agencyContract";
    const TableKey = "agencyCntID";

    public $agencyCntID;
    public $AgencyID;
    public $contractNum;
    public $title;
    public $startDate;
    public $endDate;
	public $agencyWage;
	public $selfWage;
	public $commitAmount;
	public $receiptOption;
	public $defrayTime;

    public function __construct($id = ""){
        
		$this->DT_startDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_endDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
        if ($id != ''){
            parent::FillObject($this, "
					select c.* 
                    from agencyContract c
					where c.agencyCntID = :id", array(":id" => $id));
        }
    }


    /*public static function Get($where = '', $whereParams = array(), $order = "") {

        return parent::runquery_fetchMode("
			select c.*,	b1.InfoDesc,			
				concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) PersonFullname
			from agencyContract c  
			left join BSC_persons p1 on(c.PersonID2=p1.PersonID)
		    left join BaseInfo b1 on(b1.TypeID=8 AND b1.param1=17 AND c.orgDocType=b1.InfoID)
			where 1=1  " . $where . " group by orgDocID " . $order, $whereParams);
    }*/
    public static function Get($where = '', $whereParams = array(), $order = "") {
        return parent::runquery_fetchMode("
			select c.*,	b1.InfoDesc
			from agencyContract c  
		    left join BaseInfo b1 on(b1.TypeID=8 AND b1.param1=18 AND c.AgencyID=b1.InfoID)
			where 1=1  " . $where . " group by agencyCntID " . $order, $whereParams);
    }


	/*public function Remove()
	{
		if(!DMS_documents::DeleteAllDocument($this->ContractID, "contract"))
		{
			ExceptionHandler::PushException("خطا در حذف مدارک");
	 		return false;
		}
		
		return parent::Remove();
	}*/
}


?>
