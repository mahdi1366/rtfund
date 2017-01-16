<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once getenv("DOCUMENT_ROOT") . '/office/dms/dms.class.php';

class CNT_contracts extends OperationClass {

    const TableName = "CNT_contracts";
    const TableKey = "ContractID";

    public $ContractID;
    public $TemplateID;
    public $RegPersonID;
    public $RegDate;
    public $description;
    public $PersonID;
	public $PersonID2;
    public $StartDate;
    public $EndDate;
	public $content;
	public $ContractType;
	public $LoanRequestID;
	public $ContractAmount;
	public $StatusID;

	public $_TemplateTitle;
	public $_PersonName;
    
    public function __construct($id = ""){
        
		$this->DT_StartDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_EndDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
        if ($id != ''){
            parent::FillObject($this, "
					select c.* ,  t.TemplateTitle as _TemplateTitle,
						concat_ws(' ',fname,lname,CompanyName) _PersonName
                    from CNT_contracts c
                    left join CNT_templates t using(TemplateID) 
					left join BSC_persons using(PersonID)
					where c." . static::TableKey . " = :id", array(":id" => $id));
        }
    }

    public static function Get($content = false, $where = '', $whereParams = array(), $order = "") {
		
        return parent::runquery_fetchMode("
			select c.ContractID,
				c.TemplateID,
				c.RegPersonID,
				c.RegDate,
				c.description,
				c.PersonID,
				c.PersonID2,
				c.StartDate,
				c.EndDate,
				c.StatusID,
				bf.InfoDesc StatusDesc," .
				($content ? "c.content," : "") .
				"c.ContractType,
				c.LoanRequestID,
				c.ContractAmount,
				t.TemplateTitle ,

				concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) PersonFullname,
				p1.NationalID,
				p1.address,
				p1.mobile,
				p1.WebSite,
				p1.email,
				bfp1.InfoDesc CompanyTypeDesc,
				
				concat_ws(' ',p2.fname,p2.lname,p2.CompanyName) PersonFullname2,
				p2.NationalID NationalID2,
				p2.address address2,
				p2.mobile mobile2,
				p2.WebSite WebSite2,
				p2.email email2,
				bfp2.InfoDesc CompanyTypeDesc2,

				r.RequestID,
				r.ReqAmount,
				rp.InstallmentCount,
				rp.CustomerWage
			
			from CNT_contracts c 
			join CNT_templates t using(TemplateID) 
			join BaseInfo bf on(TypeID=19 AND StatusID=InfoID)
			left join LON_requests r on(LoanRequestID=RequestID)
			left join LON_ReqParts rp on(rp.RequestID=r.RequestID)
			left join BSC_persons p1 on(c.PersonID=p1.PersonID)
			left join BaseInfo bfp1 on(bfp1.TypeID=14 AND p1.CompanyType=bfp1.InfoID)
			left join BSC_persons p2 on(c.PersonID2=p2.PersonID)
			left join BaseInfo bfp2 on(bfp2.TypeID=14 AND p2.CompanyType=bfp2.InfoID)
			
			where 1=1 " . $where . " group by ContractID " . $order, $whereParams);
    }
	
	public function Remove()
	{
		if(!DMS_documents::DeleteAllDocument($this->ContractID, "contract"))
		{
			ExceptionHandler::PushException("خطا در حذف مدارک");
	 		return false;
		}
		
		return parent::Remove();
	}
}

class CNT_ContractItems extends OperationClass {

    const TableName = "CNT_ContractItems";
    const TableKey = "ContractItemID";

    public $ContractItemID;
    public $ContractID;
    public $TemplateItemID;
    public $ItemValue;

    public function __construct($id = "") {
        parent::__construct($id);
    }

    public static function RemoveAll($ContractID, $pdo = null) {
		
        return parent::delete(static::TableName, "ContractID=:ContractID", array(":ContractID" => $ContractID), $pdo);
    }

    public static function GetContractItems($ContractID) {
        
        return parent::runquery("select * from " . static::TableName . " where ContractID=:ContractID", 
				array(":ContractID" => $ContractID));        
    }

}

class CNT_ContractSigns extends OperationClass
{
	const TableName = "CNT_ContractSigns";
	const TableKey = "SignID";
	
	public $SignID; 
	public $ContractID;
	public $PersonID;
	public $SignerPost;
	public $SignerName;

	public static function Get($where = '', $whereParams = array()) {
		
		$query = "select s.*,concat(fname,' ',lname) fullname
		from CNT_ContractSigns s
			left join BSC_persons using(PersonID)
		where 1=1 " . $where;
		
		return PdoDataAccess::runquery($query, $whereParams);
	}
}
?>
