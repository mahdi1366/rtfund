<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once 'CNTParentClass.class.php';
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
?>
