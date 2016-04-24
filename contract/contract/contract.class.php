<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once getenv("DOCUMENT_ROOT") . '/dms/dms.class.php';

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

    public static function Get($content = false, $where = '', $whereParams = array()) {
		
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
				concat_ws(' ',p2.fname,p2.lname,p2.CompanyName) PersonFullname2,
				p1.NationalID,
				p1.address,
				p1.mobile,
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
			left join BSC_persons p2 on(c.PersonID2=p2.PersonID)
			
			where 1=1 
			group by ContractID" . $where, $whereParams);
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
        
		$CntObj = new CNT_contracts($ContractID);
		
        $res_cnt = array(
			array("TemplateItemID" => 1, "ItemValue" => $CntObj->StartDate),
            array("TemplateItemID" => 2, "ItemValue" => $CntObj->EndDate),
            array("TemplateItemID" => 3, "ItemValue" => $CntObj->_PersonName)
           
        );
        $res = parent::runquery("select * from " . static::TableName . " where ContractID=:ContractID", array(":ContractID" => $ContractID));
        //  echo PdoDataAccess::GetLatestQueryString();
        return array_merge($res_cnt, $res);
    }

}

class CNT_ContractFlows extends PdoDataAccess {
    
    const TableName = "CNT_ContractFlows";
    const TableKey = "FlowID";

    /**
     * شماره یکتای ردیف جدول 
     * @var int 
     */
    public $FlowID;
    
    /**
     * منبع مربوط به انجام عمل
     * @var int 
     */
    public $ObjectType;
    /**
     * کد منبع 
     * @var int 
     */
    public $ObjectID;
    /**
     * کد فرد انجام دهنده عمل
     * @var int 
     */
    public $PersonID;
    /**
     * عمل انجام شده
     * @var int 
     */
    public $FlowAction;
    /**
     * توضیح فرد در مورد عمل انجام شده
     * @var int 
     */
    public $FlowComment;
    /**
     * تاریخ انجام عمل
     * @var int 
     */
    public $FlowDate;
    
    function __construct($id = ""){
        parent::__construct($id);
    }                
}

class CNT_ContractPayments extends OperationClass {
    
    const TableName = "CNT_ContractPayments";
    const TableKey = "PayID";

     /**
     * شماره یکتای ردیف جدول 
     * @var int 
     */
    public $PayID;  
    /**
     * 'نحوه پرداخت'
     * @var int 
     */
    public $PayType;
    /**
     * 'مبلغ پرداخت'
     * @var int 
     */
    public $PayPrice;
    /**
     * 'مبلغ جریمه'
     * @var int 
     */
    public $PenaltyPrice;
    /**
     * 'عنوان پرداخت'
     * @var int 
     */
    public $PayTitle;
    /**
     * 'وضعیت پرداخت'
     * @var int 
     */
    Public $PayStatus;
    /**
     * 'تاریخ شروع'
     * @var int 
     */
    public $StartDate;
    /**
     * 'تاریخ پایان'
     * @var int 
     */
    public $EndDate;
        
    public function __construct($id = ""){
        parent::__construct($id) ;
        
    }    
            
}

?>
