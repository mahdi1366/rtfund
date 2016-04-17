<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once 'CNTParentClass.class.php';
class CNT_ContractPayments extends CNTParentClass {
    
    const TableName = "CNT_ContractPayments";
    const TableKey = "PayId";

     /**
     * شماره یکتای ردیف جدول 
     * @var int 
     */
    public $PayId;  
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
