<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once '../global/CNTParentClass.class.php';

class CNT_contracts extends CNTParentClass {

    const TableName = "CNT_contracts";
    const TableKey = "ContractID";

    public $ContractID;
    public $TemplateID;
    public $RegPersonID;
    public $RegDate;
    public $description;
    public $PersonID;
    public $StartDate;
    public $EndDate;

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

    public static function Get($where = '', $whereParams = array()) {
		
        return parent::runquery_fetchMode("select c.* ,  t.TemplateTitle 
                                  from CNT_contracts c 
                                  join CNT_templates t using(TemplateID) 
                                  where 1=1 " . $where, $whereParams);
    }
}

class CNT_ContractItems extends CNTParentClass {

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

class CNT_ContractPayments extends CNTParentClass {
    
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
