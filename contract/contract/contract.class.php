<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once '../global/CNTParentClass.class.php';

class CNT_contracts extends CNTParentClass {

    const TableName = "CNT_contracts";
    const TableKey = "CntID";

    public $CntID;
    public $TemplateID;
    public $RegPersonID;
    public $RegDate;
    public $description;
    public $StatusCode;
    public $SupplierID;
    public $Supervisor;
    public $StartDate;
    public $EndDate;
    public $price;
    public $_TplTitle;
    
    public function __construct($id = ""){
        //parent::__construct($id) ;       
        if ($id != ''){
            parent::FillObject($this, "select c.* ,  t.TplTitle as _TplTitle
                    from CNT_contracts c
                    join CNT_templates t using(TemplateID) where c." . static::TableKey . " = :id", array(":id" => $id));
        }
    }

    public static function Get($where = '', $whereParams = array()) {
        return parent::runquery_fetchMode("select c.* ,  t.TplTitle 
                                  from CNT_contracts c 
                                  join CNT_templates t using(TemplateID) 
                                  where 1=1 " . $where, $whereParams);
    }
}

class CNT_ContractItems extends CNTParentClass {

    const TableName = "CNT_ContractItems";
    const TableKey = "CntItemID";

    public $CntItemID;
    public $CntID;
    public $TplItemID;
    public $ItemValue;

    public function __construct($id = "") {
        parent::__construct($id);
    }

    public static function RemoveAll($CntID, $pdo = null) {
		
        $res = parent::delete(static::TableName, "CntID=:CntID", array(":CntID" => $CntID), $pdo);
        return $res;
    }

    public static function GetContractItems($CntID) {
        $CntObj = new CNT_contracts($CntID);
        $res_cnt = array(array("TplItemID" => 1, "ItemValue" => $CntObj->SupplierID),
            array("TplItemID" => 2, "ItemValue" => $CntObj->Supervisor),
            array("TplItemID" => 3, "ItemValue" => $CntObj->StartDate),
            array("TplItemID" => 4, "ItemValue" => $CntObj->EndDate),
            array("TplItemID" => 5, "ItemValue" => $CntObj->price)
        );
        $res = parent::runquery("select * from " . static::TableName . " where CntID=:CntID", array(":CntID" => $CntID));
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
